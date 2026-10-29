<?php

namespace Tests\Feature;

use App\Filament\Pages\Settings as SettingsPage;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function enable(string $message = 'Upgrading servers until 6pm.'): void
    {
        Settings::set('maintenance_enabled', true);
        Settings::set('maintenance_message', $message);
    }

    public function test_site_is_normal_when_maintenance_is_off(): void
    {
        $this->get('/')->assertOk()->assertDontSee('be back soon');
    }

    public function test_visitors_see_the_maintenance_page_on_every_public_route(): void
    {
        $this->enable();

        foreach (['/', '/articles', '/projects', '/login'] as $path) {
            $this->get($path)
                ->assertStatus(503)
                ->assertHeader('Retry-After', '3600')
                ->assertSee('be back soon')
                ->assertSee('Upgrading servers until 6pm.');
        }
    }

    public function test_enquiries_cannot_be_submitted_during_maintenance(): void
    {
        $this->enable();

        $this->post('/enquiries', ['name' => 'X', 'email' => 'x@example.test', 'message' => 'A long enough message'])
            ->assertStatus(503);

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_customers_are_also_shown_the_maintenance_page(): void
    {
        $this->enable();

        $this->actingAs(User::factory()->create())->get('/account')->assertStatus(503);
    }

    public function test_admins_can_still_browse_the_site_with_a_reminder(): void
    {
        $this->enable();

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/')
            ->assertOk()
            ->assertSee('Maintenance mode is on.');
    }

    public function test_admin_panel_login_and_health_check_stay_available(): void
    {
        $this->enable();

        $this->get('/admin/login')->assertOk();
        $this->get('/up')->assertOk();
        $this->get('/api/v1/health')->assertOk();
    }

    public function test_admin_can_switch_maintenance_on_and_off_in_settings(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(SettingsPage::class)
            ->fillForm(['maintenance_enabled' => true, 'maintenance_message' => 'Back at noon.'])
            ->call('save')
            ->assertHasNoFormErrors();

        auth()->logout();
        $this->get('/')->assertStatus(503)->assertSee('Back at noon.');

        $this->actingAs(User::factory()->create(['is_admin' => true]));
        Livewire::test(SettingsPage::class)
            ->fillForm(['maintenance_enabled' => false])
            ->call('save');

        auth()->logout();
        $this->get('/')->assertOk();
    }
}
