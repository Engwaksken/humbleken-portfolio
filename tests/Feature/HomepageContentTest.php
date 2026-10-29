<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_services_page_is_linked_from_the_homepage(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('href="'.route('services.index').'"', false);
    }

    public function test_active_services_are_listed_under_their_active_category(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Network Support', 'is_active' => true]);
        Service::factory()->create([
            'service_category_id' => $category->id,
            'name' => 'Wi-Fi coverage audit',
            'summary' => 'Find and fix dead zones.',
        ]);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSeeInOrder(['Network Support', 'Wi-Fi coverage audit', 'Find and fix dead zones.'])
            ->assertDontSee('Recurring technical issues');
    }

    public function test_inactive_services_are_hidden(): void
    {
        Service::factory()->create(['name' => 'Visible service']);
        Service::factory()->create(['name' => 'Hidden service', 'is_active' => false]);

        $this->get(route('services.index'))
            ->assertSee('Visible service')
            ->assertDontSee('Hidden service');
    }

    public function test_services_in_an_inactive_category_are_listed_without_its_heading(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Retired Category', 'is_active' => false]);
        Service::factory()->create(['service_category_id' => $category->id, 'name' => 'Orphaned service']);

        $this->get(route('services.index'))
            ->assertSee('Orphaned service')
            ->assertDontSee('Retired Category');
    }

    public function test_brand_and_contact_settings_are_rendered(): void
    {
        Settings::set('brand_name', 'Acme Tech Help');
        Settings::set('contact_email', 'hello@example.test');
        Settings::set('social_links', ['linkedin' => 'https://linkedin.example.test/acme', 'x' => '  ']);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('<title>Discuss your technical requirements. — Acme Tech Help', false)
            ->assertSee('hello@example.test')
            ->assertSee('https://linkedin.example.test/acme')
            ->assertDontSee('Contact details are being added.');
    }
}
