<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicesAccordionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function category(string $name, int $order, array $services): ServiceCategory
    {
        $category = ServiceCategory::factory()->create(['name' => $name, 'slug' => str($name)->slug(), 'sort_order' => $order, 'is_active' => true]);

        foreach ($services as $index => $service) {
            // Fixed order: the factory otherwise picks a random sort_order.
            Service::factory()->create(['name' => $service, 'service_category_id' => $category->id, 'is_active' => true, 'sort_order' => $index]);
        }

        return $category;
    }

    public function test_each_area_is_a_collapsible_group_with_the_first_one_open(): void
    {
        $this->category('Networking', 1, ['Office network setup', 'Wi-Fi survey']);
        $this->category('Cybersecurity', 2, ['Security audit']);
        $this->category('Empty area', 3, []);
        Service::factory()->create(['name' => 'Odd job', 'service_category_id' => null, 'is_active' => true]);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('id="service-groups"', false)
            ->assertSee('<details id="category-networking" class="service-group group/acc card overflow-hidden scroll-mt-28"  open >', false)
            ->assertSee('<details id="category-cybersecurity" class="service-group group/acc card overflow-hidden scroll-mt-28" >', false)
            ->assertSeeInOrder(['Networking', '2 services', 'Office network setup', 'Wi-Fi survey', 'Cybersecurity', '1 service', 'Security audit', 'Other services', 'Odd job'])
            ->assertSee('href="#category-cybersecurity"', false)
            ->assertSee('data-services-toggle-all', false)
            ->assertSee('4 services in 3 areas.')
            ->assertDontSee('Empty area');
    }

    public function test_a_single_area_is_shown_without_collapsing(): void
    {
        $this->category('Networking', 1, ['Office network setup']);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Office network setup')
            ->assertDontSee('service-group', false)
            ->assertDontSee('data-services-toggle-all', false);
    }

    public function test_categories_without_active_services_show_the_empty_message(): void
    {
        $this->category('Networking', 1, []);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Services are being updated.');
    }

    public function test_headers_get_the_entrance_effect(): void
    {
        $this->get(route('services.index'))->assertSee('hero-enter', false)->assertSee('hero-glow', false);
        $this->get(route('home'))->assertSee('hero-enter', false);
    }
}
