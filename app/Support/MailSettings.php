<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;
use Throwable;

/**
 * Outgoing email (SMTP) configured from admin Settings → Email.
 *
 * When enabled, these settings replace the .env mailer so that sign-up verification,
 * password resets and notifications are actually delivered. When disabled, the .env
 * MAIL_* configuration is used unchanged.
 */
class MailSettings
{
    public const MAILER = 'site_smtp';

    public const ENCRYPTIONS = [
        'tls' => 'STARTTLS (usually port 587)',
        'ssl' => 'SSL/TLS (usually port 465)',
        'none' => 'None (not recommended)',
    ];

    public static function enabled(): bool
    {
        return (bool) Settings::get('mail_enabled')
            && filled(Settings::get('mail_host'))
            && filled(Settings::get('mail_from_address'));
    }

    public static function password(): ?string
    {
        $ciphertext = Settings::get('mail_password_ciphertext');

        if (blank($ciphertext)) {
            return null;
        }

        try {
            return Crypt::decryptString($ciphertext);
        } catch (Throwable $exception) {
            // The APP_KEY changed since the password was saved; it must be re-entered.
            report($exception);

            return null;
        }
    }

    /**
     * Laravel mailer configuration for the given SMTP values.
     *
     * @param  array{host?: ?string, port?: mixed, encryption?: ?string, username?: ?string, password?: ?string}  $values
     * @return array<string, mixed>
     */
    public static function mailerConfig(array $values): array
    {
        $encryption = $values['encryption'] ?? 'tls';

        return [
            'transport' => 'smtp',
            // "smtps" connects with implicit TLS (port 465); "smtp" upgrades with STARTTLS when offered.
            'scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
            'host' => trim((string) ($values['host'] ?? '')),
            'port' => (int) ($values['port'] ?? 0) ?: ($encryption === 'ssl' ? 465 : 587),
            'username' => filled($values['username'] ?? null) ? trim((string) $values['username']) : null,
            'password' => filled($values['password'] ?? null) ? (string) $values['password'] : null,
            'timeout' => 20,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST) ?: null,
            'auto_tls' => $encryption !== 'none',
        ];
    }

    /**
     * Point Laravel's mail configuration at the admin SMTP settings when they are enabled.
     */
    public static function apply(): void
    {
        try {
            if (! static::enabled()) {
                return;
            }

            config([
                'mail.mailers.'.self::MAILER => static::mailerConfig([
                    'host' => Settings::get('mail_host'),
                    'port' => Settings::get('mail_port'),
                    'encryption' => Settings::get('mail_encryption', 'tls'),
                    'username' => Settings::get('mail_username'),
                    'password' => static::password(),
                ]),
                'mail.default' => self::MAILER,
                'mail.from.address' => Settings::get('mail_from_address'),
                'mail.from.name' => Settings::get('mail_from_name') ?: (Settings::get('brand_name') ?: config('app.name')),
            ]);
        } catch (Throwable $exception) {
            // The settings table may not exist yet (fresh install, migrations); fall back to .env.
            report($exception);
        }
    }

    /**
     * Plain-language description of where email currently goes, for the admin screen.
     */
    public static function describeCurrentDelivery(): string
    {
        if (static::enabled()) {
            return 'Emails are currently sent through the SMTP server below.';
        }

        $mailer = (string) config('mail.default');

        return match ($mailer) {
            'log' => 'Emails are NOT being delivered: the server mail setting is "log", so messages are only written to the log file. Enter your SMTP details below and enable them so visitors receive sign-up confirmations and password resets.',
            'array' => 'Emails are NOT being delivered: the server mail setting is "array" (testing only).',
            default => "Emails are currently sent using the server's \"{$mailer}\" mail setting from the .env file. Enable the settings below to use a different SMTP server instead.",
        };
    }
}
