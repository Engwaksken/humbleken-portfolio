<?php

namespace Tests\Feature;

use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingsHelperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_get_returns_the_default_for_a_missing_key(): void
    {
        $this->assertSame('fallback', Settings::get('missing-key', 'fallback'));
    }

    public function test_set_persists_a_value_and_get_returns_it(): void
    {
        Settings::set('brand_name', 'Acme');

        $this->assertSame('Acme', Settings::get('brand_name', 'default'));
        $this->assertDatabaseHas('settings', [
            'key' => 'brand_name',
            'value' => 'Acme',
        ]);
    }

    public function test_set_stores_an_array_as_json(): void
    {
        Settings::set('social_links', ['github' => 'https://github.com/x']);

        $stored = Settings::get('social_links');

        $this->assertSame(
            ['github' => 'https://github.com/x'],
            json_decode($stored, true),
        );
    }

    public function test_all_returns_a_key_value_map(): void
    {
        Settings::set('brand_name', 'Acme');

        $all = Settings::all();

        $this->assertArrayHasKey('brand_name', $all);
        $this->assertSame('Acme', $all['brand_name']);
    }
}
