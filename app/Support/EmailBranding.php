<?php

namespace App\Support;

/**
 * Brand details used by every email and push notification: name, logo, colour,
 * sign-off and footer. Logo, colour and contact details come from Settings; the
 * email-only options live in Settings, Email.
 */
class EmailBranding
{
    public static function name(): string
    {
        return rescue(fn () => Settings::get('brand_name'), null, report: false) ?: (string) config('app.name');
    }

    public static function color(): string
    {
        return rescue(fn () => Theme::color(), Theme::DEFAULT_COLOR, report: false);
    }

    /** Absolute logo URL for emails, or null to show the brand name as text. */
    public static function logoUrl(): ?string
    {
        return rescue(function (): ?string {
            if (! (bool) Settings::get('email_show_logo', true)) {
                return null;
            }

            return Settings::publicFileUrl('logo');
        }, null, report: false);
    }

    /** Small square image for push notifications: the favicon, else the logo. */
    public static function iconUrl(): ?string
    {
        return rescue(fn () => Settings::publicFileUrl('favicon') ?: Settings::publicFileUrl('logo'), null, report: false);
    }

    /** Sign-off name, e.g. "Kind regards, Ken". */
    public static function signature(): string
    {
        return rescue(fn () => Settings::get('email_signature'), null, report: false)
            ?: (rescue(fn () => Settings::get('owner_name'), null, report: false) ?: static::name());
    }

    /**
     * Contact details shown under every email.
     *
     * @return array<int, string>
     */
    public static function contactLines(): array
    {
        return rescue(fn (): array => array_values(array_filter([
            Settings::get('contact_email'),
            Settings::get('contact_phone'),
            Settings::get('address'),
        ], 'filled')), [], report: false);
    }

    public static function footerText(): ?string
    {
        return rescue(fn () => Settings::get('email_footer_text'), null, report: false) ?: null;
    }
}
