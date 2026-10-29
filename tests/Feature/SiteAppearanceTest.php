<?php

namespace Tests\Feature;

use App\Filament\Pages\Settings as SettingsPage;
use App\Models\User;
use App\Support\ImageCompressor;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SiteAppearanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    /**
     * A real 1x1 PNG, so the upload passes image validation without needing the GD extension.
     */
    private function pngUpload(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='),
        );
    }

    public function test_raster_uploads_are_compressed_to_webp_when_gd_is_available(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD is required to test image compression.');
        }

        $compressed = ImageCompressor::compress($this->pngUpload('logo.png')->getRealPath(), 'image/png');

        $this->assertNotNull($compressed);
        $this->assertStringStartsWith('RIFF', $compressed);
        $this->assertSame('WEBP', substr($compressed, 8, 4));
        $this->assertNull(ImageCompressor::compress(__FILE__, 'application/pdf'));
    }

    public function test_default_theme_uses_system_font_and_no_colour_override(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('--font-sans: ui-sans-serif', false)
            ->assertSee('html { font-size: 100%; }', false)
            ->assertDontSee('--color-brand:', false)
            ->assertDontSee('fonts.bunny.net', false);
    }

    public function test_custom_colour_font_and_size_are_applied(): void
    {
        Settings::set('theme_color', '#1d4ed8');
        Settings::set('theme_font', 'inter');
        Settings::set('theme_font_size', 'large');

        $this->get('/')
            ->assertSee('--color-brand: #1d4ed8;', false)
            ->assertSee('--color-brand-dark:', false)
            ->assertSee('--color-brand-tint:', false)
            ->assertSee("--font-sans: 'Inter'", false)
            ->assertSee('https://fonts.bunny.net/css?family=inter', false)
            ->assertSee('html { font-size: 112.5%; }', false);
    }

    public function test_custom_secondary_colour_and_font_family_are_applied(): void
    {
        Settings::set('theme_secondary_color', '#123456');
        Settings::set('theme_custom_font', 'My Custom Font');

        $this->get('/')
            ->assertSee('--color-secondary: #123456;', false)
            ->assertSee("--font-sans: 'My Custom Font', ui-sans-serif", false);
    }

    public function test_stored_unreadable_colour_falls_back_to_default(): void
    {
        Settings::set('theme_color', '#ffff00');

        $this->get('/')->assertDontSee('#ffff00', false);
    }

    public function test_logo_and_favicon_are_shown_when_set(): void
    {
        Storage::disk('public')->put('branding/logo.png', 'png');
        Storage::disk('public')->put('branding/icon.png', 'png');
        Settings::set('brand_name', 'Acme IT');
        Settings::set('logo', 'branding/logo.png');
        Settings::set('favicon', 'branding/icon.png');

        $this->get('/')
            ->assertSee('<img src="'.route('site-assets.show', 'logo').'" alt="Acme IT"', false)
            ->assertSee('<link rel="icon" href="'.route('site-assets.show', 'favicon').'">', false);
    }

    public function test_branding_assets_are_served_without_a_public_storage_symlink(): void
    {
        Storage::disk('public')->put('branding/logo.png', 'logo bytes');
        Settings::set('logo', 'branding/logo.png');

        $this->get(route('site-assets.show', 'logo'))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Cache-Control', 'max-age=3600, public');
    }

    public function test_missing_logo_file_falls_back_to_brand_text(): void
    {
        Settings::set('logo', 'branding/gone.png');

        $this->get('/')->assertDontSee('<img src=', false);
    }

    public function test_admin_can_save_appearance_and_upload_branding(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(SettingsPage::class)
            ->fillForm([
                'theme_color' => '#7a1f1f',
                'theme_secondary_color' => '#123456',
                'theme_font' => 'georgia',
                'theme_custom_font' => 'Arial',
                'theme_font_size' => 'small',
                'logo' => $this->pngUpload('logo.png'),
                'favicon' => UploadedFile::fake()->create('favicon.ico', 2, 'image/x-icon'),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('#7a1f1f', Settings::get('theme_color'));
        $this->assertSame('#123456', Settings::get('theme_secondary_color'));
        $this->assertSame('georgia', Settings::get('theme_font'));
        $this->assertSame('Arial', Settings::get('theme_custom_font'));
        $this->assertSame('small', Settings::get('theme_font_size'));
        Storage::disk('public')->assertExists(Settings::get('logo'));
        $this->assertIsString(Settings::get('favicon'));
        Storage::disk('public')->assertExists(Settings::get('favicon'));
    }

    public function test_unreadable_colour_is_rejected_in_settings(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(SettingsPage::class)
            ->fillForm(['theme_color' => '#ffd400'])
            ->call('save')
            ->assertHasFormErrors(['theme_color']);

        $this->assertNull(Settings::get('theme_color'));
    }

    public function test_svg_logos_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(SettingsPage::class)
            ->fillForm(['logo' => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')])
            ->call('save')
            ->assertHasFormErrors(['logo']);
    }

    public function test_replacing_the_logo_deletes_the_old_file(): void
    {
        Storage::disk('public')->put('branding/old.png', 'png');
        Settings::set('logo', 'branding/old.png');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(SettingsPage::class)
            ->fillForm(['logo' => null])
            ->call('save')
            ->assertHasNoFormErrors();

        Storage::disk('public')->assertMissing('branding/old.png');
    }

    public function test_admin_panel_uses_branding(): void
    {
        Settings::set('brand_name', 'Acme IT');

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/admin')
            ->assertOk()
            ->assertSee('Acme IT');
    }
}
