<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Auth\MultiFactor\Http\Middleware\EnsureMultiFactorAuthenticationIsEnabled;
use Illuminate\Http\Request;

class EnsureAdminMfaForTicketAttachments
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->user()?->is_admin && config('app.admin_mfa_required')) {
            return app(EnsureMultiFactorAuthenticationIsEnabled::class)->handle($request, $next);
        }

        return $next($request);
    }
}
