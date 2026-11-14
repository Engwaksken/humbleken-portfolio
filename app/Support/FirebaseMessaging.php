<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Sends mobile app push notifications through Firebase Cloud Messaging (HTTP v1 API).
 *
 * The Firebase service account key is pasted in admin Settings, Mobile app and stored
 * encrypted. Access tokens are signed locally with OpenSSL, so no extra package is needed.
 */
class FirebaseMessaging
{
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    private const TOKEN_CACHE_KEY = 'firebase.access_token';

    /** Result of send() when Firebase says the device token is no longer valid. */
    public const TOKEN_INVALID = 'invalid';

    public static function configured(): bool
    {
        return static::serviceAccount() !== null;
    }

    /**
     * @return array{project_id: string, client_email: string, private_key: string, token_uri?: string}|null
     */
    public static function serviceAccount(): ?array
    {
        $ciphertext = rescue(fn () => Settings::get('fcm_service_account_ciphertext'), null, report: false);

        if (blank($ciphertext)) {
            return null;
        }

        try {
            return static::parseServiceAccount(Crypt::decryptString($ciphertext));
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * Validate pasted service account JSON. Returns null when it is not a usable key.
     *
     * @return array{project_id: string, client_email: string, private_key: string, token_uri?: string}|null
     */
    public static function parseServiceAccount(string $json): ?array
    {
        $data = json_decode(trim($json), true);

        if (! is_array($data)
            || ($data['type'] ?? null) !== 'service_account'
            || blank($data['project_id'] ?? null)
            || blank($data['client_email'] ?? null)
            || ! str_contains((string) ($data['private_key'] ?? ''), 'PRIVATE KEY')) {
            return null;
        }

        return $data;
    }

    /**
     * A short-lived OAuth access token for the FCM API.
     *
     * @param  array<string, mixed>|null  $account  Defaults to the stored service account.
     */
    public static function accessToken(?array $account = null, bool $fresh = false): string
    {
        $account ??= static::serviceAccount() ?? throw new RuntimeException('Firebase is not configured.');

        if (! $fresh && $account === static::serviceAccount() && ($cached = Cache::get(self::TOKEN_CACHE_KEY))) {
            return $cached;
        }

        $now = time();
        $tokenUri = $account['token_uri'] ?? 'https://oauth2.googleapis.com/token';
        $segments = [
            static::base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])),
            static::base64Url(json_encode([
                'iss' => $account['client_email'],
                'scope' => self::SCOPE,
                'aud' => $tokenUri,
                'iat' => $now,
                'exp' => $now + 3600,
            ])),
        ];

        $key = openssl_pkey_get_private($account['private_key']);
        if ($key === false || ! openssl_sign(implode('.', $segments), $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('The Firebase private key could not be read.');
        }
        $segments[] = static::base64Url($signature);

        $response = Http::asForm()->acceptJson()->timeout(15)->post($tokenUri, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => implode('.', $segments),
        ]);

        $token = $response->json('access_token');
        if (! $response->successful() || blank($token)) {
            throw new RuntimeException('Google rejected the Firebase service account (HTTP '.$response->status().'): '.($response->json('error_description') ?? $response->json('error') ?? 'no details'));
        }

        Cache::put(self::TOKEN_CACHE_KEY, $token, now()->addSeconds(max(60, (int) $response->json('expires_in', 3600) - 300)));

        return $token;
    }

    public static function forgetAccessToken(): void
    {
        Cache::forget(self::TOKEN_CACHE_KEY);
    }

    /**
     * Send one notification to one device.
     *
     * @param  array<string, string>  $data  Extra string values for the app, e.g. the screen to open.
     * @return string|bool true when sent, false on a temporary failure, TOKEN_INVALID when the token should be removed.
     */
    public static function send(string $deviceToken, string $title, string $body, array $data = []): string|bool
    {
        $account = static::serviceAccount();
        if ($account === null) {
            return false;
        }

        $response = Http::withToken(static::accessToken())
            ->acceptJson()
            ->timeout(15)
            ->post("https://fcm.googleapis.com/v1/projects/{$account['project_id']}/messages:send", [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => ['title' => $title, 'body' => $body],
                    'data' => array_map('strval', $data),
                    'android' => ['priority' => 'high'],
                ],
            ]);

        if ($response->successful()) {
            return true;
        }

        $errorCode = collect($response->json('error.details', []))->pluck('errorCode')->filter()->first();
        if ($response->status() === 404 || in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'], true)) {
            return self::TOKEN_INVALID;
        }

        if ($response->status() === 401) {
            static::forgetAccessToken();
        }

        report(new RuntimeException('Firebase push failed (HTTP '.$response->status().'): '.$response->body()));

        return false;
    }

    private static function base64Url(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
