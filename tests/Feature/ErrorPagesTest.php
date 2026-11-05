<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.debug' => false]);
    }

    public function test_missing_page_shows_branded_404(): void
    {
        Settings::set('brand_name', 'Acme Tech Help');

        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertSee('Error 404')
            ->assertSee('Acme Tech Help')
            ->assertSee('Go to the home page')
            ->assertSee('<meta name="robots" content="noindex">', false);
    }

    public function test_customer_opening_admin_sees_403_with_sign_out_option(): void
    {
        $this->actingAs(User::factory()->create(['email' => 'customer@example.test', 'is_admin' => false]));

        $this->get('/admin')
            ->assertForbidden()
            ->assertSee('Access denied')
            ->assertSee('customer@example.test')
            ->assertSee('Sign out')
            ->assertSee(route('logout'), false);
    }

    public function test_guest_403_does_not_show_sign_out(): void
    {
        Route::get('/__forbidden', fn () => abort(403));

        $this->get('/__forbidden')->assertForbidden()->assertSee('Access denied')->assertDontSee('You are signed in as');
    }

    public function test_expired_session_and_rate_limit_pages(): void
    {
        Route::get('/__expired', fn () => abort(419));
        Route::get('/__busy', fn () => abort(429));

        $this->get('/__expired')->assertStatus(419)->assertSee('Page expired');
        $this->get('/__busy')->assertStatus(429)->assertSee('Too many requests')->assertSee('Try again');
    }

    public function test_server_error_hides_exception_details(): void
    {
        Route::get('/__boom', fn () => throw new RuntimeException('secret internal detail'));

        $this->get('/__boom')
            ->assertStatus(500)
            ->assertSee('Something went wrong')
            ->assertDontSee('secret internal detail');
    }

    public function test_server_error_page_renders_when_database_is_unavailable(): void
    {
        Route::get('/__db-down', function () {
            // Make every settings lookup fail, as it would with a broken database.
            Schema::drop('settings');
            Cache::flush();
            throw new RuntimeException('database is down');
        });

        $this->get('/__db-down')
            ->assertStatus(500)
            ->assertSee('Something went wrong')
            ->assertSee(config('app.name'));
    }

    public function test_unlisted_codes_use_generic_fallback_pages(): void
    {
        Route::get('/__teapot', fn () => abort(418));
        Route::get('/__not-implemented', fn () => abort(501));

        $this->get('/__teapot')->assertStatus(418)->assertSee('Error 418')->assertSee('This request could not be completed');
        $this->get('/__not-implemented')->assertStatus(501)->assertSee('Error 501')->assertSee('Something went wrong');
    }
}
