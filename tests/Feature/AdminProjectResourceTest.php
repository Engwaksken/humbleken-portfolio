<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProjectResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_create_and_edit_projects(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $project = Project::factory()->create(['title' => 'Draft in progress']);

        $this->actingAs($admin)->get('/admin/projects')->assertOk()->assertSee('Draft in progress');
        $this->actingAs($admin)->get('/admin/projects/create')->assertOk();
        $this->actingAs($admin)->get("/admin/projects/{$project->id}/edit")->assertOk();
    }

    public function test_non_admin_cannot_manage_projects(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin/projects')->assertForbidden();
        $this->actingAs($user)->get('/admin/projects/create')->assertForbidden();
    }

    public function test_guest_is_redirected_from_projects(): void
    {
        $this->get('/admin/projects')->assertRedirect('/admin/login');
    }
}
