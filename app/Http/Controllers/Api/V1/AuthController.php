<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\AccountDeletion;
use App\Support\NotificationPreferences;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * Token authentication for customers using the mobile app.
 */
class AuthController extends Controller
{
    public function register(Request $request, CreateNewUser $creator): JsonResponse
    {
        $request->validate(['device_name' => ['required', 'string', 'max:100']]);

        $user = $creator->create($request->only(['name', 'email', 'password', 'password_confirmation']));
        event(new Registered($user));

        return response()->json([
            'token' => $this->issueToken($user, (string) $request->input('device_name')),
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        // Administrator accounts are protected by two-factor authentication in the admin panel;
        // a password-only token would bypass it.
        if ($user->is_admin) {
            throw ValidationException::withMessages(['email' => 'Administrator accounts must sign in through the admin panel.']);
        }

        return response()->json([
            'token' => $this->issueToken($user, $credentials['device_name']),
            'user' => new UserResource($user),
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);

        // The same reply whether or not the address has an account, so accounts cannot be discovered.
        Password::sendResetLink($request->only('email'));

        return response()->json(['message' => 'If an account exists for that email address, a password reset link has been sent.'], 202);
    }

    public function user(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Permanently delete the signed-in customer's account and linked data.
     */
    public function deleteAccount(Request $request): Response
    {
        $request->validate(['password' => ['required', 'string']]);

        $user = $request->user();

        if (! Hash::check((string) $request->input('password'), $user->password)) {
            throw ValidationException::withMessages(['password' => 'The password is incorrect.']);
        }

        AccountDeletion::delete($user);

        return response()->noContent();
    }

    /**
     * Update the customer's email notification choices. Only the events and
     * channels sent are changed.
     */
    public function updateNotificationPreferences(Request $request): UserResource
    {
        $request->validate([
            'notification_preferences' => ['required', 'array'],
            'notification_preferences.*' => ['array'],
            'notification_preferences.*.*' => ['boolean'],
        ]);

        $user = $request->user();
        $sent = collect($request->input('notification_preferences'))
            ->only(array_keys(NotificationPreferences::CUSTOMER_EVENTS))
            ->map(fn (array $channels): array => collect($channels)
                ->only(array_keys(NotificationPreferences::CUSTOMER_CHANNELS))
                ->map(fn (mixed $value): bool => (bool) $value)
                ->all())
            ->all();

        $user->notification_preferences = array_replace_recursive($user->notification_preferences ?? [], $sent);
        $user->save();

        return new UserResource($user);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
        }

        return response()->json(['message' => 'A new verification link has been sent to your email address.'], 202);
    }

    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    private function issueToken(User $user, string $deviceName): string
    {
        $minutes = config('sanctum.expiration');

        return $user->createToken(
            $deviceName,
            ['customer'],
            $minutes ? now()->addMinutes((int) $minutes) : null,
        )->plainTextToken;
    }
}
