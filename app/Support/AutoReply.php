<?php

namespace App\Support;

/**
 * Automatic acknowledgement emails sent to a client after their first enquiry or
 * appointment request. Text is edited in admin Settings, Auto replies.
 */
class AutoReply
{
    public const TYPES = ['enquiry', 'booking'];

    public const DEFAULTS = [
        'enquiry' => [
            'subject' => 'I have received your enquiry',
            'body' => "Hello {name},\n\nThank you for getting in touch. I have received your enquiry and will reply within one working day.\n\nKind regards,\n{brand}",
        ],
        'booking' => [
            'subject' => 'Your appointment request {reference}',
            'body' => "Hello {name},\n\nThank you for your appointment request for {preferred_time}. Your reference is {reference}. I will confirm the time with you shortly.\n\nYou can check the status at any time here: {track_url}\n\n{payment_details}\n\nKind regards,\n{brand}",
        ],
    ];

    public const PLACEHOLDERS = '{name}, {email}, {service}, {reference}, {preferred_time}, {track_url}, {brand}, {contact_phone}, {payment_details}';

    public static function enabled(string $type): bool
    {
        // On by default: a new install acknowledges clients until it is switched off.
        return (bool) Settings::get("auto_reply_{$type}_enabled", true);
    }

    public static function subject(string $type): string
    {
        return Settings::get("auto_reply_{$type}_subject") ?: self::DEFAULTS[$type]['subject'];
    }

    public static function body(string $type): string
    {
        return Settings::get("auto_reply_{$type}_body") ?: self::DEFAULTS[$type]['body'];
    }

    /**
     * Replace placeholders. Unknown placeholders are left as typed so mistakes are visible.
     *
     * @param  array<string, ?string>  $values
     */
    public static function render(string $template, array $values): string
    {
        $values += [
            'brand' => Settings::get('brand_name') ?: config('app.name'),
            'contact_phone' => Settings::get('contact_phone') ?: '',
            'payment_details' => PaymentSettings::instructionsText(),
        ];

        $replacements = [];
        foreach ($values as $key => $value) {
            $replacements['{'.$key.'}'] = (string) $value;
        }

        $text = str_replace("\r\n", "\n", strtr($template, $replacements));

        // Collapse blank lines left by empty placeholders such as {payment_details}.
        return trim((string) preg_replace("/\n{3,}/", "\n\n", $text));
    }
}
