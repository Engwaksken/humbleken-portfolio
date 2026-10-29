<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_service_categories_and_services_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('settings'));
        $this->assertTrue(Schema::hasTable('service_categories'));
        $this->assertTrue(Schema::hasTable('services'));
    }

    public function test_services_table_has_a_service_category_id_column(): void
    {
        $this->assertTrue(Schema::hasColumn('services', 'service_category_id'));
    }

    public function test_service_categories_table_has_a_slug_column(): void
    {
        $this->assertTrue(Schema::hasColumn('service_categories', 'slug'));
    }

    public function test_enquiries_table_exists_with_status_and_service_columns(): void
    {
        $this->assertTrue(Schema::hasTable('enquiries'));
        $this->assertTrue(Schema::hasColumns('enquiries', ['service_id', 'user_id', 'status']));
    }

    public function test_new_tables_can_be_rolled_back_and_re_migrated(): void
    {
        $this->artisan('migrate:rollback', ['--step' => 4])->assertSuccessful();

        $this->assertFalse(Schema::hasTable('enquiries'));

        $this->assertFalse(Schema::hasTable('services'));
        $this->assertFalse(Schema::hasTable('service_categories'));
        $this->assertFalse(Schema::hasTable('settings'));

        $this->artisan('migrate')->assertSuccessful();

        $this->assertTrue(Schema::hasTable('enquiries'));
        $this->assertTrue(Schema::hasTable('services'));
        $this->assertTrue(Schema::hasTable('service_categories'));
        $this->assertTrue(Schema::hasTable('settings'));
    }
}
