<?php

namespace App\Http\Middleware;

use App\Support\Settings;
use App\Support\Theme;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shows visitors a maintenance page when switched on in admin Settings, while the
 * admin panel stays usable and signed-in administrators can still preview the site.
 */
class PublicMaintenanceMode
{
    /** Paths that must keep working so administrators can switch maintenance off again. */
    private const ALWAYS_AVAILABLE = ['admin', 'admin/*', 'livewire/*', 'filament/*', 'up'];

    public static function isEnabled(): bool
    {
        return (bool) Settings::get('maintenance_enabled');
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! static::isEnabled() || $request->is(self::ALWAYS_AVAILABLE) || $request->user()?->is_admin) {
            return $next($request);
        }

        return response()
            ->view('maintenance', [
                'brandName' => Settings::get('brand_name') ?: 'IT Problem Solver',
                'message' => Settings::get('maintenance_message') ?: 'We are making some improvements and will be back shortly.',
                'faviconUrl' => Settings::publicFileUrl('favicon'),
                'themeTags' => Theme::headTags(),
            ], 503)
            ->header('Retry-After', '3600');
    }
}
