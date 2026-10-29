<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticlePublishingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_only_published_articles_are_listed(): void
    {
        Article::factory()->published()->create(['title' => 'Fixing slow Wi-Fi']);
        Article::factory()->create(['title' => 'Unfinished draft']);
        Article::factory()->scheduled()->create(['title' => 'Next week post']);

        $this->get('/articles')
            ->assertOk()
            ->assertSee('Fixing slow Wi-Fi')
            ->assertDontSee('Unfinished draft')
            ->assertDontSee('Next week post');
    }

    public function test_empty_state_is_shown_when_nothing_is_published(): void
    {
        $this->get('/articles')->assertOk()->assertSee('Articles are being added.');
    }

    public function test_published_article_page_renders_with_metadata(): void
    {
        $category = ArticleCategory::factory()->create(['name' => 'Networking', 'slug' => 'networking']);
        $article = Article::factory()->published()->create([
            'article_category_id' => $category->id,
            'title' => 'Choosing a router',
            'slug' => 'choosing-a-router',
            'excerpt' => 'What matters for a small office.',
            'body' => "## Throughput\n\nMeasure first.",
        ]);
        $article->tags()->attach(Tag::factory()->create(['name' => 'hardware', 'slug' => 'hardware']));

        $this->get('/articles/choosing-a-router')
            ->assertOk()
            ->assertSee('<title>Choosing a router', false)
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('article:published_time', false)
            ->assertSee('<h2>Throughput</h2>', false)
            ->assertSee('1 min read')
            ->assertSee(route('articles.index', ['category' => 'networking']), false)
            ->assertSee(route('articles.index', ['tag' => 'hardware']), false);
    }

    public function test_draft_and_scheduled_article_pages_are_not_found(): void
    {
        Article::factory()->create(['slug' => 'a-draft']);
        Article::factory()->scheduled()->create(['slug' => 'a-scheduled']);

        $this->get('/articles/a-draft')->assertNotFound();
        $this->get('/articles/a-scheduled')->assertNotFound();
    }

    public function test_raw_html_in_article_body_is_not_rendered(): void
    {
        Article::factory()->published()->create([
            'slug' => 'unsafe',
            'body' => "<script>alert('x')</script>\n\n[click](javascript:alert(1))",
        ]);

        $this->get('/articles/unsafe')
            ->assertOk()
            ->assertDontSee('<script>alert', false)
            ->assertDontSee('javascript:alert', false);
    }

    public function test_search_matches_title_excerpt_and_body_case_insensitively(): void
    {
        Article::factory()->published()->create(['title' => 'Backup basics', 'excerpt' => null, 'body' => 'x']);
        Article::factory()->published()->create(['title' => 'Other', 'excerpt' => 'All about BACKUP rotation', 'body' => 'x']);
        Article::factory()->published()->create(['title' => 'Third', 'excerpt' => null, 'body' => 'Test your backups.']);
        Article::factory()->published()->create(['title' => 'Unrelated printing tips', 'excerpt' => null, 'body' => 'Paper.']);
        Article::factory()->create(['title' => 'Draft about backup']);

        $this->get('/articles?q=backup')
            ->assertOk()
            ->assertSee('Backup basics')
            ->assertSee('Other')
            ->assertSee('Third')
            ->assertSee('3 articles')
            ->assertDontSee('Unrelated printing tips')
            ->assertDontSee('Draft about backup');
    }

    public function test_search_with_no_results_offers_a_hint(): void
    {
        Article::factory()->published()->create(['title' => 'Something']);

        $this->get('/articles?q=nothing-matches-this')
            ->assertOk()
            ->assertSee('No articles')
            ->assertSee('Try a different search term');
    }

    public function test_articles_can_be_filtered_by_category_and_tag(): void
    {
        $networking = ArticleCategory::factory()->create(['name' => 'Networking', 'slug' => 'networking']);
        $security = ArticleCategory::factory()->create(['name' => 'Security', 'slug' => 'security']);
        $tag = Tag::factory()->create(['name' => 'smb', 'slug' => 'smb']);

        $inNetworking = Article::factory()->published()->create(['article_category_id' => $networking->id, 'title' => 'Network article']);
        Article::factory()->published()->create(['article_category_id' => $security->id, 'title' => 'Security article']);
        $inNetworking->tags()->attach($tag);

        $this->get('/articles?category=networking')
            ->assertSee('Network article')
            ->assertDontSee('Security article');

        $this->get('/articles?tag=smb')
            ->assertSee('Network article')
            ->assertDontSee('Security article');
    }

    public function test_categories_without_public_articles_are_not_offered(): void
    {
        $empty = ArticleCategory::factory()->create(['name' => 'Empty Category']);
        Article::factory()->create(['article_category_id' => $empty->id]);
        Article::factory()->published()->create();

        $this->get('/articles')->assertDontSee('Empty Category');
    }

    public function test_overlong_search_terms_are_rejected(): void
    {
        $this->get('/articles?q='.str_repeat('a', 101))->assertRedirect();
    }

    public function test_nav_link_appears_only_when_articles_are_public(): void
    {
        $this->get('/')->assertDontSee('href="'.route('articles.index').'"', false);

        Article::factory()->published()->create();

        $this->get('/')->assertSee('href="'.route('articles.index').'"', false);
    }

    public function test_sitemap_lists_only_public_content(): void
    {
        Article::factory()->published()->create(['slug' => 'public-article']);
        Article::factory()->create(['slug' => 'draft-article']);
        Project::factory()->published()->create(['slug' => 'public-project']);
        Project::factory()->scheduled()->create(['slug' => 'scheduled-project']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('home'), false)
            ->assertSee(route('articles.show', ['article' => 'public-article']), false)
            ->assertSee(route('projects.show', ['project' => 'public-project']), false)
            ->assertDontSee('draft-article')
            ->assertDontSee('scheduled-project');
    }
}
