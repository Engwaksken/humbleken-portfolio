<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Filament\Resources\ProjectResource\Pages\ManageProjects;
use App\Models\Project;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProjectResourceTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_admin_can_list_projects(): void
    {
        Project::factory()->create(['title' => 'Draft in progress']);
        $this->actingAsAdmin();

        $this->get('/admin/projects')->assertOk()->assertSee('Draft in progress');
    }

    public function test_admin_can_create_a_project_in_a_modal(): void
    {
        $this->actingAsAdmin();

        Livewire::test(ManageProjects::class)
            ->callAction('create', data: [
                'title' => 'Office network refresh',
                'slug' => 'office-network-refresh',
                'status' => PublicationStatus::Draft->value,
                'sort_order' => 0,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('projects', ['slug' => 'office-network-refresh', 'status' => 'draft']);
    }

    public function test_create_modal_validates_input(): void
    {
        $this->actingAsAdmin();

        Livewire::test(ManageProjects::class)
            ->callAction('create', data: ['title' => '', 'slug' => 'Not A Slug!'])
            ->assertHasActionErrors(['title' => 'required', 'slug']);
    }

    public function test_admin_can_edit_and_publish_a_project_in_a_modal(): void
    {
        $project = Project::factory()->create();
        $this->actingAsAdmin();

        Livewire::test(ManageProjects::class)
            ->callAction(TestAction::make('edit')->table($project), data: ['status' => PublicationStatus::Published->value])
            ->assertHasNoActionErrors();

        $this->assertTrue($project->fresh()->isPublished());
    }

    public function test_admin_can_delete_a_project(): void
    {
        $project = Project::factory()->create();
        $this->actingAsAdmin();

        Livewire::test(ManageProjects::class)->callAction(TestAction::make('delete')->table($project));

        $this->assertModelMissing($project);
    }

    public function test_view_on_site_is_offered_only_for_published_projects(): void
    {
        $published = Project::factory()->published()->create(['slug' => 'live-one']);
        $draft = Project::factory()->create();
        $this->actingAsAdmin();

        Livewire::test(ManageProjects::class)
            ->assertActionVisible(TestAction::make('viewPublic')->table($published))
            ->assertActionHasUrl(TestAction::make('viewPublic')->table($published), route('projects.show', ['project' => 'live-one']))
            ->assertActionHidden(TestAction::make('viewPublic')->table($draft));
    }

    public function test_non_admin_cannot_manage_projects(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/admin/projects')
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_projects(): void
    {
        $this->get('/admin/projects')->assertRedirect('/admin/login');
    }
}
