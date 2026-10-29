<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_category_has_many_services(): void
    {
        $category = ServiceCategory::factory()->create();
        $services = Service::factory()->count(2)->create([
            'service_category_id' => $category->id,
        ]);

        $categoryServices = $category->services;

        $this->assertCount(2, $categoryServices);
        $this->assertEqualsCanonicalizing(
            $services->pluck('id')->all(),
            $categoryServices->pluck('id')->all(),
        );
    }

    public function test_a_service_belongs_to_a_category(): void
    {
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create([
            'service_category_id' => $category->id,
        ]);

        $this->assertTrue($service->category->is($category));
    }

    public function test_a_service_may_have_no_category(): void
    {
        $service = Service::factory()->create();

        $this->assertNull($service->category);
    }

    public function test_is_active_casts_to_boolean_and_sort_order_to_integer(): void
    {
        $service = Service::factory()->create();
        $category = ServiceCategory::factory()->create();

        $service->refresh();
        $category->refresh();

        $this->assertIsBool($service->is_active);
        $this->assertIsInt($service->sort_order);
        $this->assertIsBool($category->is_active);
        $this->assertIsInt($category->sort_order);
    }

    public function test_indicative_price_casts_to_a_decimal_string(): void
    {
        $service = Service::factory()->create([
            'indicative_price' => '199.99',
        ]);

        $this->assertSame('199.99', $service->indicative_price);
    }
}
