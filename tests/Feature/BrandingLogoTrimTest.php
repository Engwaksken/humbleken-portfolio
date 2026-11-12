<?php

namespace Tests\Feature;

use App\Support\ImageCompressor;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandingLogoTrimTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD is required.');
        }

        Cache::flush();
    }

    /** A 1200x600 white canvas with a 300x100 dark block at (450, 250). */
    private function paddedLogoPng(): string
    {
        $image = imagecreatetruecolor(1200, 600);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
        imagefilledrectangle($image, 450, 250, 749, 349, imagecolorallocate($image, 20, 38, 61));
        ob_start();
        imagepng($image);
        imagedestroy($image);

        return (string) ob_get_clean();
    }

    public function test_compress_trims_blank_margins_when_asked(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'logo');
        file_put_contents($path, $this->paddedLogoPng());

        [$width, $height] = getimagesizefromstring(ImageCompressor::compress($path, 'image/png', trimMargins: true));
        $this->assertGreaterThanOrEqual(300, $width);
        $this->assertLessThan(340, $width, 'only a slim padding is kept');
        $this->assertGreaterThanOrEqual(100, $height);
        $this->assertLessThan(140, $height);

        [$width, $height] = getimagesizefromstring(ImageCompressor::compress($path, 'image/png'));
        $this->assertSame([1200, 600], [$width, $height], 'untouched without trimming');

        unlink($path);
    }

    public function test_command_trims_the_stored_logo_and_skips_missing_ones(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('branding/old.png', $this->paddedLogoPng());
        Settings::set('logo', 'branding/old.png');

        $this->artisan('branding:trim-logos')
            ->expectsOutputToContain('logo: trimmed to')
            ->expectsOutputToContain('sticky_logo: not uploaded, skipped.')
            ->assertSuccessful();

        $path = Settings::get('logo');
        $this->assertStringStartsWith('branding/', $path);
        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertMissing('branding/old.png');
        [$width] = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertLessThan(340, $width);
    }
}
