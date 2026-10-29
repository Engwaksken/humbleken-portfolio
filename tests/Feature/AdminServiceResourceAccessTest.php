<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminServiceResourceAccessTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }

    public function test_admin_can_view_service_categories(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/service-categories')
            ->assertOk();
    }

    public function test_admin_can_view_services(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/services')
            ->assertOk();
    }

    public function test_admin_can_view_settings(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/settings')
            ->assertOk();
    }

    public function test_non_admin_cannot_view_service_categories(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/service-categories')
            ->assertForbidden();
    }

    public function test_non_admin_cannot_view_services(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/services')
            ->assertForbidden();
    }

    public function test_non_admin_cannot_view_settings(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/settings')
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_service_categories(): void
    {
        $this->get('/admin/service-categories')
            ->assertRedirect('/admin/login');
    }

    public function test_guest_is_redirected_from_services(): void
    {
        $this->get('/admin/services')
            ->assertRedirect('/admin/login');
    }

    public function test_guest_is_redirected_from_settings(): void
    {
        $this->get('/admin/settings')
            ->assertRedirect('/admin/login');
    }
}
