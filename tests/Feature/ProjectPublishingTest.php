<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPublishingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_published_projects_are_listed(): void
    {
        Project::factory()->published()->create(['title' => 'Clinic network rebuild']);

        $this->get('/projects')
            ->assertOk()
            ->assertSee('Clinic network rebuild');
    }

    public function test_drafts_and_scheduled_projects_are_not_listed(): void
    {
        Project::factory()->create(['title' => 'Secret draft']);
        Project::factory()->scheduled()->create(['title' => 'Future launch']);

        $this->get('/projects')
            ->assertOk()
            ->assertDontSee('Secret draft')
            ->assertDontSee('Future launch')
            ->assertSee('Projects are being added.');
    }

    public function test_published_project_page_renders(): void
    {
        Project::factory()->published()->create([
            'title' => 'Warehouse Wi-Fi',
            'slug' => 'warehouse-wifi',
            'summary' => 'Coverage across three buildings.',
            'technologies' => ['Ubiquiti', 'VLANs'],
            'body' => "## Outcome\n\nFewer dropouts.",
        ]);

        $this->get('/projects/warehouse-wifi')
            ->assertOk()
            ->assertSee('<title>Warehouse Wi-Fi', false)
            ->assertSee('<meta name="description" content="Coverage across three buildings.">', false)
            ->assertSee('<h2>Outcome</h2>', false)
            ->assertSee('VLANs');
    }

    public function test_draft_and_scheduled_project_pages_are_not_found(): void
    {
        Project::factory()->create(['slug' => 'a-draft']);
        Project::factory()->scheduled()->create(['slug' => 'a-scheduled']);

        $this->get('/projects/a-draft')->assertNotFound();
        $this->get('/projects/a-scheduled')->assertNotFound();
        $this->get('/projects/does-not-exist')->assertNotFound();
    }

    public function test_raw_html_and_unsafe_links_in_the_body_are_not_rendered(): void
    {
        Project::factory()->published()->create([
            'slug' => 'unsafe',
            'body' => "<script>alert('x')</script>\n\n[click](javascript:alert(1))",
        ]);

        $this->get('/projects/unsafe')
            ->assertOk()
            ->assertDontSee('<script>alert', false)
            ->assertDontSee('javascript:alert', false);
    }

    public function test_publishing_without_a_date_publishes_immediately(): void
    {
        $project = Project::factory()->create();

        $project->update(['status' => ProjectStatus::Published]);

        $this->assertNotNull($project->published_at);
        $this->assertTrue($project->isPublished());
    }

    public function test_homepage_shows_published_projects_and_nav_link_only_when_available(): void
    {
        $this->get('/')
            ->assertDontSee('Recent work.')
            ->assertDontSee('href="'.route('projects.index').'"', false);

        Project::factory()->published()->create(['title' => 'Visible on home']);
        Project::factory()->create(['title' => 'Hidden draft']);

        $this->get('/')
            ->assertSee('Recent work.')
            ->assertSee('Visible on home')
            ->assertDontSee('Hidden draft')
            ->assertSee('href="'.route('projects.index').'"', false);
    }
}
