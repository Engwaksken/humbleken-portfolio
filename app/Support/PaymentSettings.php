<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Payment options configured in admin Settings, Payments: ioTec Pay (mobile money),
 * bank transfer and manual mobile money numbers.
 */
class PaymentSettings
{
    public const IOTEC_DEFAULT_TOKEN_URL = 'https://id.iotec.io/connect/token';

    public const IOTEC_DEFAULT_API_URL = 'https://pay.iotec.io/api';

    public static function iotecEnabled(): bool
    {
        return (bool) Settings::get('iotec_enabled')
            && filled(Settings::get('iotec_client_id'))
            && filled(Settings::get('iotec_wallet_id'))
            && filled(static::iotecClientSecret());
    }

    public static function iotecClientSecret(): ?string
    {
        $ciphertext = Settings::get('iotec_client_secret_ciphertext');

        if (blank($ciphertext)) {
            return null;
        }

        try {
            return Crypt::decryptString($ciphertext);
        } catch (Throwable $exception) {
            // The APP_KEY changed since the secret was saved; it must be re-entered.
            report($exception);

            return null;
        }
    }

    public static function iotecTokenUrl(): string
    {
        return Settings::get('iotec_token_url') ?: self::IOTEC_DEFAULT_TOKEN_URL;
    }

    public static function iotecApiUrl(): string
    {
        return rtrim(Settings::get('iotec_api_url') ?: self::IOTEC_DEFAULT_API_URL, '/');
    }

    /**
     * Request an ioTec access token with the client credentials grant.
     *
     * @return array{ok: bool, message: string, token?: string}
     */
    public static function requestIotecToken(string $tokenUrl, string $clientId, string $clientSecret): array
    {
        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(15)
                ->post($tokenUrl, [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'client_credentials',
                ]);
        } catch (Throwable $exception) {
            report($exception);

            return ['ok' => false, 'message' => 'Could not reach ioTec. Check the token URL and that this server can make outgoing HTTPS connections.'];
        }

        if ($response->successful() && filled($token = $response->json('access_token'))) {
            return ['ok' => true, 'message' => 'ioTec accepted the client ID and secret.', 'token' => $token];
        }

        return ['ok' => false, 'message' => match ($response->status()) {
            400, 401 => 'ioTec rejected the client ID or secret (HTTP '.$response->status().'). Copy them again from the ioTec Pay dashboard.',
            404 => 'The token URL was not found (HTTP 404). Check it against the ioTec documentation.',
            default => 'ioTec returned HTTP '.$response->status().'. Check the token URL and try again.',
        }];
    }

    public static function bankEnabled(): bool
    {
        return (bool) Settings::get('bank_enabled') && filled(Settings::get('bank_account_number'));
    }

    public static function mobileMoneyEnabled(): bool
    {
        return (bool) Settings::get('mobile_money_enabled') && filled(Settings::get('mobile_money_number'));
    }

    /**
     * Plain-text summary of the manual payment options, used in client emails.
     */
    public static function instructionsText(): string
    {
        $blocks = [];

        if (static::bankEnabled()) {
            $lines = ['Bank transfer'];
            foreach ([
                'Bank' => 'bank_name',
                'Account name' => 'bank_account_name',
                'Account number' => 'bank_account_number',
                'Branch' => 'bank_branch',
                'SWIFT code' => 'bank_swift',
            ] as $label => $key) {
                if (filled($value = Settings::get($key))) {
                    $lines[] = "{$label}: {$value}";
                }
            }
            $blocks[] = implode("\n", $lines);
        }

        if (static::mobileMoneyEnabled()) {
            $lines = ['Mobile money'];
            if (filled($provider = Settings::get('mobile_money_provider'))) {
                $lines[] = "Network: {$provider}";
            }
            $lines[] = 'Number: '.Settings::get('mobile_money_number');
            if (filled($name = Settings::get('mobile_money_name'))) {
                $lines[] = "Registered name: {$name}";
            }
            $blocks[] = implode("\n", $lines);
        }

        if (filled($notes = Settings::get('payment_instructions'))) {
            $blocks[] = $notes;
        }

        return implode("\n\n", $blocks);
    }
}
