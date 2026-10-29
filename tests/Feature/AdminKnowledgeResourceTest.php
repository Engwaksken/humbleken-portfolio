<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'create article' => ['/admin/articles/create'],
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

    public function test_admin_can_edit_an_article(): void
    {
        $article = Article::factory()->create();

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get("/admin/articles/{$article->id}/edit")
            ->assertOk();
    }
}
