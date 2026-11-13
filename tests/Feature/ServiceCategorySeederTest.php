<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCategorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_every_category_once_and_keeps_admin_edits(): void
    {
        $this->seed(ServiceCategorySeeder::class);
        ServiceCategory::where('slug', 'cybersecurity')->update(['name' => 'Security', 'is_active' => false]);
        $this->seed(ServiceCategorySeeder::class);

        $this->assertSame(count(ServiceCategorySeeder::CATEGORIES), ServiceCategory::count());
        $this->assertSame(['Security', false], [
            ServiceCategory::where('slug', 'cybersecurity')->value('name'),
            (bool) ServiceCategory::where('slug', 'cybersecurity')->value('is_active'),
        ]);
        $this->assertSame('it-consulting-strategy', ServiceCategory::orderBy('sort_order')->value('slug'));
    }

    public function test_empty_categories_stay_off_the_services_page(): void
    {
        $this->withoutVite();
        $this->seed(ServiceCategorySeeder::class);
        $networking = ServiceCategory::where('slug', 'network-design-installation')->sole();
        Service::factory()->create(['name' => 'Office Wi-Fi survey', 'service_category_id' => $networking->id, 'is_active' => true]);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Network Design &amp; Installation', false)
            ->assertSee('Office Wi-Fi survey')
            ->assertDontSee('Mobile App Development');
    }
}
