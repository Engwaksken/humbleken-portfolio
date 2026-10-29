<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

/**
 * Sends administrators to the admin panel and customers to their account.
 */
class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        /** @var Request $request */
        if ($request->wantsJson()) {
            return new JsonResponse(['two_factor' => false]);
        }

        $default = $request->user()?->is_admin
            ? Filament::getPanel('admin')->getUrl()
            : config('fortify.home');

        return redirect()->intended($default);
    }
}
