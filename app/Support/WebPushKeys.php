<?php

namespace App\Support;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Crypt;
use Minishlink\WebPush\WebPush;
use RuntimeException;
use Throwable;

/**
 * VAPID keys that identify this site to browser push services. They are generated
 * once, on first use, and stored in settings (the private key encrypted).
 */
class WebPushKeys
{
    /** Whether the PHP library that encrypts and sends push messages is installed. */
    public static function libraryInstalled(): bool
    {
        return class_exists(WebPush::class);
    }

    public static function publicKey(): string
    {
        return static::keys()['public'];
    }

    /**
     * @return array{public: string, private: string}
     */
    public static function keys(): array
    {
        $public = Settings::get('webpush_public_key');
        $private = Settings::get('webpush_private_key_ciphertext');

        if (filled($public) && filled($private)) {
            try {
                return ['public' => $public, 'private' => Crypt::decryptString($private)];
            } catch (Throwable $exception) {
                // The APP_KEY changed; generate a new pair (browsers will subscribe again).
                report($exception);
            }
        }

        $keys = static::generate();
        Settings::set('webpush_public_key', $keys['public']);
        Settings::set('webpush_private_key_ciphertext', Crypt::encryptString($keys['private']));
        // Old subscriptions were made for the previous public key and can no longer be used.
        PushSubscription::query()->delete();

        return $keys;
    }

    /**
     * Create a P-256 key pair in the base64url format the Push API expects.
     *
     * @return array{public: string, private: string}
     */
    public static function generate(): array
    {
        $options = ['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC];
        $key = @openssl_pkey_new($options);

        // PHP on Windows (for example XAMPP) needs the bundled openssl.cnf to create keys.
        if ($key === false) {
            foreach (array_filter([getenv('OPENSSL_CONF'), dirname(PHP_BINARY).'/extras/ssl/openssl.cnf']) as $config) {
                if (is_file($config) && ($key = @openssl_pkey_new($options + ['config' => $config])) !== false) {
                    break;
                }
            }
        }

        if ($key === false) {
            throw new RuntimeException('OpenSSL could not create push notification keys: '.openssl_error_string());
        }

        $details = openssl_pkey_get_details($key);
        $pad = fn (string $bytes): string => str_pad($bytes, 32, "\0", STR_PAD_LEFT);

        return [
            'public' => static::base64Url("\x04".$pad($details['ec']['x']).$pad($details['ec']['y'])),
            'private' => static::base64Url($pad($details['ec']['d'])),
        ];
    }

    private static function base64Url(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
