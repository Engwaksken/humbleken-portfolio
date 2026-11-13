<?php

namespace Tests\Feature;

use App\Enums\HeroImagePosition;
use App\Filament\Resources\ArticleResource\Pages\ManageArticles;
use App\Filament\Resources\ServiceResource\Pages\ManageServices;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Service;
use App\Models\Tag;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ArticlePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    private function image(string $path): string
    {
        Storage::disk('public')->put($path, UploadedFile::fake()->image('photo.png', 40, 30)->getContent());

        return $path;
    }

    private function article(array $attributes = []): Article
    {
        $category = ArticleCategory::factory()->create(['name' => 'Networking', 'slug' => 'networking']);

        return Article::factory()->published()->create($attributes + [
            'article_category_id' => $category->id,
            'title' => 'Five signs your network needs attention',
            'excerpt' => 'Slow transfers and dropped calls point to the same few problems.',
            'body' => "Intro paragraph.\n\n## Slow afternoons\n\nText.\n\n## Dead spots\n\nMore text.\n\n## Dead spots\n\nRepeated heading.",
        ]);
    }

    public function test_article_page_has_breadcrumbs_byline_contents_and_structured_data(): void
    {
        Settings::set('owner_name', 'Ken Waks');
        Settings::set('owner_role', 'IT Engineer');
        Settings::set('owner_bio', 'I fix networks and explain them in plain language.');
        $article = $this->article();

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSeeInOrder(['Home', 'Articles', 'Networking'])
            ->assertSee('Ken Waks')
            ->assertSee('IT Engineer')
            ->assertSee('About the author')
            ->assertSee('I fix networks and explain them in plain language.')
            // Every section heading gets an anchor, kept unique, and is listed under "On this page".
            ->assertSee('<h2 id="slow-afternoons">Slow afternoons</h2>', false)
            ->assertSee('<h2 id="dead-spots-2">Dead spots</h2>', false)
            ->assertSee('href="#dead-spots"', false)
            ->assertSee('On this page')
            ->assertSee('"@type":"BlogPosting"', false)
            ->assertSee('"author":{"@type":"Person","name":"Ken Waks"', false)
            ->assertSee('Share on LinkedIn')
            ->assertSee('data-copy-link="'.route('articles.show', $article->slug).'"', false)
            ->assertSee('data-reading-progress', false);
    }

    public function test_short_articles_skip_the_contents_list_and_use_the_brand_when_no_name_is_set(): void
    {
        Settings::set('brand_name', 'HumbleKen');
        $article = $this->article(['body' => "Just one paragraph.\n\n## Only heading\n\nText."]);

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertDontSee('On this page')
            ->assertSee('"name":"HumbleKen"', false);
    }

    public function test_related_articles_share_a_category_or_tag(): void
    {
        $article = $this->article();
        $tag = Tag::factory()->create(['name' => 'Backups', 'slug' => 'backups']);
        $article->tags()->attach($tag);

        $sameCategory = Article::factory()->published()->create(['title' => 'Same category post', 'article_category_id' => $article->article_category_id]);
        $sameTag = Article::factory()->published()->create(['title' => 'Same tag post']);
        $sameTag->tags()->attach($tag);
        Article::factory()->published()->create(['title' => 'Unrelated post']);
        Article::factory()->create(['title' => 'Draft in same category', 'article_category_id' => $article->article_category_id]);

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee('Related articles')
            ->assertSee('Same category post')
            ->assertSee('Same tag post')
            ->assertDontSee('Unrelated post')
            ->assertDontSee('Draft in same category');
    }

    public function test_article_image_can_go_below_beside_or_behind_the_title(): void
    {
        $article = $this->article([
            'featured_image' => $this->image('article-images/cover.png'),
            'featured_image_alt' => 'Patch panel',
        ]);
        $page = fn () => $this->get(route('articles.show', $article->slug))->assertOk();

        // Default: a full-width figure under the header.
        $page()->assertSee('<figure class="container-site', false)->assertDontSee('hero-on-image', false);

        $article->update(['image_position' => HeroImagePosition::Right]);
        $page()->assertDontSee('<figure class="container-site', false)->assertSee('lg:col-span-5', false)->assertDontSee('lg:order-last', false);

        $article->update(['image_position' => HeroImagePosition::Left]);
        $page()->assertSee('lg:order-last', false);

        $article->update(['image_position' => HeroImagePosition::Background]);
        $page()->assertSee('hero-on-image', false)->assertSee('alt="Patch panel"', false)->assertDontSee('<figure class="container-site', false);
    }

    public function test_service_image_defaults_beside_the_title_and_can_move(): void
    {
        $service = Service::factory()->create(['name' => 'Network audit', 'is_active' => true, 'sample_image' => $this->image('service-samples/audit.png')]);
        $page = fn () => $this->get(route('services.show', $service->slug))->assertOk();

        $page()->assertSee('lg:col-span-5', false)->assertDontSee('hero-on-image', false);

        $service->update(['image_position' => HeroImagePosition::Background]);
        $page()->assertSee('hero-on-image', false);

        $service->update(['image_position' => HeroImagePosition::Below]);
        $page()->assertSee('<figure class="container-site', false)->assertDontSee('hero-on-image', false);
    }

    public function test_admin_can_choose_the_image_position(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $article = Article::factory()->published()->create();
        $service = Service::factory()->create();

        Livewire::test(ManageArticles::class)
            ->mountTableAction('edit', $article)
            ->setTableActionData(['image_position' => 'background'])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        Livewire::test(ManageServices::class)
            ->mountTableAction('edit', $service)
            ->setTableActionData(['image_position' => 'left'])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertSame(HeroImagePosition::Background, $article->fresh()->image_position);
        $this->assertSame(HeroImagePosition::Left, $service->fresh()->image_position);
    }

    public function test_owner_photo_is_served_and_shown_on_articles(): void
    {
        Settings::set('owner_photo', $this->image('branding/me.png'));
        $article = $this->article();

        $this->get(route('site-assets.show', ['key' => 'owner_photo']))->assertOk();
        $this->get(route('articles.show', $article->slug))->assertSee(route('site-assets.show', ['key' => 'owner_photo']), false);
    }
}
