<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Filament\Resources\ArticleResource\Pages\ManageArticles;
use App\Models\Article;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminKnowledgeResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public static function adminPages(): array
    {
        return [
            'articles' => ['/admin/articles'],
            'article categories' => ['/admin/article-categories'],
            'tags' => ['/admin/tags'],
        ];
    }

    #[DataProvider('adminPages')]
    public function test_admin_can_open_knowledge_pages(string $path): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get($path)
            ->assertOk();
    }

    #[DataProvider('adminPages')]
    public function test_non_admin_cannot_open_knowledge_pages(string $path): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get($path)
            ->assertForbidden();
    }

    #[DataProvider('adminPages')]
    public function test_guest_is_redirected_from_knowledge_pages(string $path): void
    {
        $this->get($path)->assertRedirect('/admin/login');
    }

    public function test_admin_can_create_an_article_with_a_new_tag_in_modals(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(ManageArticles::class)
            ->callAction('create', data: [
                'title' => 'Choosing a backup strategy',
                'slug' => 'choosing-a-backup-strategy',
                'status' => PublicationStatus::Draft->value,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('articles', ['slug' => 'choosing-a-backup-strategy']);
    }

    public function test_admin_can_edit_and_delete_an_article_in_modals(): void
    {
        $article = Article::factory()->create();
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(ManageArticles::class)
            ->callAction(TestAction::make('edit')->table($article), data: ['title' => 'Renamed article'])
            ->assertHasNoActionErrors();
        $this->assertSame('Renamed article', $article->fresh()->title);

        Livewire::test(ManageArticles::class)->callAction(TestAction::make('delete')->table($article));
        $this->assertModelMissing($article);
    }
}
