<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Filament\Pages\Settings as SettingsPage;
use App\Filament\Resources\ArticleResource\Pages\ManageArticles;
use App\Filament\Resources\ProjectResource\Pages\ManageProjects;
use App\Filament\Resources\ServiceResource\Pages\ManageServices;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTabbedFormsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_project_form_is_split_into_tabs(): void
    {
        Livewire::test(ManageProjects::class)
            ->mountAction('create')
            ->assertMountedActionModalSee(['Details', 'Write-up', 'Publishing']);
    }

    public function test_article_form_is_split_into_tabs(): void
    {
        Livewire::test(ManageArticles::class)
            ->mountAction('create')
            ->assertMountedActionModalSee(['Details', 'Body', 'Organisation', 'Publishing']);
    }

    public function test_service_form_is_split_into_tabs(): void
    {
        Livewire::test(ManageServices::class)
            ->mountAction('create')
            ->assertMountedActionModalSee(['Details', 'Delivery & pricing', 'Visibility']);
    }

    public function test_fields_from_every_tab_are_saved_together(): void
    {
        Livewire::test(ManageProjects::class)
            ->callAction('create', data: [
                'title' => 'Tabbed project',
                'slug' => 'tabbed-project',
                'body' => '## From the write-up tab',
                'status' => PublicationStatus::Published->value,
                'sort_order' => 3,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('projects', [
            'slug' => 'tabbed-project',
            'body' => '## From the write-up tab',
            'status' => 'published',
            'sort_order' => 3,
        ]);
    }

    public function test_errors_on_any_tab_block_saving(): void
    {
        // Title is on the first tab, status on the last; both must still be validated.
        Livewire::test(ManageProjects::class)
            ->callAction('create', data: ['title' => '', 'slug' => 'ok-slug', 'status' => null])
            ->assertHasActionErrors(['title' => 'required', 'status' => 'required']);

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_settings_page_uses_tabs_and_saves_fields_from_each(): void
    {
        Livewire::test(SettingsPage::class)
            ->assertSee(['Site identity', 'Contact details', 'Social links'])
            ->fillForm([
                'brand_name' => 'Tabbed Brand',
                'contact_email' => 'hello@example.test',
                'social_links' => ['linkedin' => 'https://linkedin.example.test/me'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Tabbed Brand', Settings::get('brand_name'));
        $this->assertSame('hello@example.test', Settings::get('contact_email'));
    }
}
