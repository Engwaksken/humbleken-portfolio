<?php

namespace Tests\Feature;

use App\Filament\Pages\Settings as SettingsPage;
use App\Models\User;
use App\Support\MailSettings;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function enableSmtp(array $overrides = []): void
    {
        foreach ([
            'mail_enabled' => true,
            'mail_host' => 'smtp.example.test',
            'mail_port' => 465,
            'mail_encryption' => 'ssl',
            'mail_username' => 'sender@example.test',
            'mail_from_address' => 'no-reply@example.test',
            'mail_from_name' => 'Acme Support',
            ...$overrides,
        ] as $key => $value) {
            Settings::set($key, $value);
        }
        Settings::set('mail_password_ciphertext', Crypt::encryptString('smtp-secret'));
    }

    public function test_enabled_admin_smtp_settings_become_the_default_mailer(): void
    {
        $this->enableSmtp();

        // Resolving the mail manager (as sending any email does) applies the settings.
        app()->forgetInstance('mail.manager');
        app('mail.manager');

        $this->assertSame(MailSettings::MAILER, config('mail.default'));
        $this->assertSame('smtp.example.test', config('mail.mailers.site_smtp.host'));
        $this->assertSame(465, config('mail.mailers.site_smtp.port'));
        $this->assertSame('smtps', config('mail.mailers.site_smtp.scheme'));
        $this->assertSame('sender@example.test', config('mail.mailers.site_smtp.username'));
        $this->assertSame('smtp-secret', config('mail.mailers.site_smtp.password'));
        $this->assertSame('no-reply@example.test', config('mail.from.address'));
        $this->assertSame('Acme Support', config('mail.from.name'));
    }

    public function test_starttls_uses_plain_smtp_scheme(): void
    {
        $config = MailSettings::mailerConfig(['host' => 'smtp.example.test', 'encryption' => 'tls']);

        $this->assertSame('smtp', $config['scheme']);
        $this->assertSame(587, $config['port']);
        $this->assertTrue($config['auto_tls']);
    }

    public function test_disabled_settings_leave_the_env_mailer_in_place(): void
    {
        $this->enableSmtp(['mail_enabled' => false]);
        $default = config('mail.default');

        MailSettings::apply();

        $this->assertSame($default, config('mail.default'));
        $this->assertFalse(MailSettings::enabled());
    }

    public function test_admin_saves_smtp_settings_with_encrypted_password(): void
    {
        Livewire::test(SettingsPage::class)
            ->fillForm([
                'mail_enabled' => true,
                'mail_host' => 'smtp.example.test',
                'mail_encryption' => 'tls',
                'mail_port' => 587,
                'mail_username' => 'sender@example.test',
                'mail_password' => 'smtp-secret',
                'mail_from_address' => 'no-reply@example.test',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Settings saved');

        $ciphertext = Settings::get('mail_password_ciphertext');
        $this->assertNotSame('smtp-secret', $ciphertext);
        $this->assertSame('smtp-secret', MailSettings::password());
        $this->assertNull(Settings::get('mail_password'));
        $this->assertTrue(MailSettings::enabled());
    }

    public function test_enabling_without_server_or_sender_is_rejected(): void
    {
        Livewire::test(SettingsPage::class)
            ->fillForm(['mail_enabled' => true, 'mail_host' => '', 'mail_from_address' => ''])
            ->call('save')
            ->assertNotified('Email settings are incomplete');

        $this->assertFalse(MailSettings::enabled());
    }

    public function test_stored_password_is_not_sent_to_the_settings_form(): void
    {
        $this->enableSmtp();

        $component = Livewire::test(SettingsPage::class);

        $this->assertArrayNotHasKey('mail_password_ciphertext', $component->get('data'));
        $this->assertEmpty($component->get('data.mail_password'));
    }

    public function test_admin_can_send_a_test_email_with_unsaved_settings(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(SettingsPage::class)
            ->fillForm([
                'mail_host' => 'smtp.example.test',
                'mail_port' => 587,
                'mail_encryption' => 'tls',
                'mail_from_address' => 'no-reply@example.test',
            ])
            ->call('sendTestEmail')
            ->assertNotified('Test email sent');
    }

    public function test_unreachable_smtp_server_reports_a_helpful_error(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        // Nothing listens on port 1, so the connection is refused immediately.
        Livewire::test(SettingsPage::class)
            ->fillForm([
                'mail_host' => '127.0.0.1',
                'mail_port' => 1,
                'mail_encryption' => 'none',
                'mail_from_address' => 'no-reply@example.test',
            ])
            ->call('sendTestEmail')
            ->assertNotified('The test email could not be sent');
    }
}
