<?php

namespace App\Providers\Filament;

use App\Support\Settings;
use App\Support\Theme;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(isSimple: false)
            ->navigationItems([
                NavigationItem::make('My profile')
                    ->url(fn (): string => Filament::getProfileUrl())
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->group('Configuration')
                    ->sort(90)
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.auth.profile')),
            ])
            ->renderHook(PanelsRenderHook::STYLES_AFTER, fn (): View => view('filament.admin-styles'))
            ->multiFactorAuthentication(
                [AppAuthentication::make()->recoverable()],
                isRequired: fn (): bool => (bool) config('app.admin_mfa_required'),
            )
            // Branding follows admin Settings; rescue() keeps the panel usable before migrations run.
            ->brandName(fn (): string => rescue(fn () => Settings::get('brand_name'), null, report: false) ?: config('app.name'))
            ->brandLogo(fn (): ?string => rescue(fn () => Settings::publicFileUrl('logo'), null, report: false))
            ->brandLogoHeight('2.25rem')
            ->favicon(fn (): ?string => rescue(fn () => Settings::publicFileUrl('favicon'), null, report: false))
            ->colors(fn (): array => [
                'primary' => Color::hex(rescue(fn () => Theme::color(), Theme::DEFAULT_COLOR, report: false)),
                'secondary' => Color::hex(rescue(fn () => Theme::secondaryColor(), Theme::DEFAULT_SECONDARY_COLOR, report: false)),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
