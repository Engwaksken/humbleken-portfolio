<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_main_site_pages_have_independent_urls_and_admin_backed_copy(): void
    {
        Settings::set('services_title', 'Our managed services');
        Settings::set('approach_title', 'A measurable approach');

        $this->get(route('services.index'))->assertOk()->assertSee('Our managed services');
        $this->get(route('approach'))->assertOk()->assertSee('A measurable approach');
        $this->get(route('booking'))->assertOk()->assertSee('Request a consultation');
        $this->get(route('contact'))->assertOk()->assertSee('Discuss your technical requirements');
    }

    public function test_active_services_have_individual_pages_and_inactive_services_are_hidden(): void
    {
        $service = Service::factory()->create(['name' => 'Network review', 'slug' => 'network-review', 'is_active' => true]);
        Service::factory()->create(['slug' => 'inactive-service', 'is_active' => false]);

        $this->get(route('services.index'))->assertOk()->assertSee('Network review')->assertDontSee('inactive-service');
        $this->get(route('services.show', $service->slug))->assertOk()->assertSee('Network review');
        $this->get(route('services.show', 'inactive-service'))->assertNotFound();
    }
}
