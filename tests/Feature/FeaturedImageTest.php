<?php

namespace Tests\Feature;

use App\Filament\Resources\ArticleResource\Pages\ManageArticles;
use App\Models\Article;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FeaturedImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    private function storeImage(string $path): string
    {
        Storage::disk('public')->put($path, UploadedFile::fake()->image('photo.png', 40, 30)->getContent());

        return $path;
    }

    private function articleWithImage(array $attributes = []): Article
    {
        return Article::factory()->published()->create($attributes + [
            'title' => 'Fixing slow Wi-Fi',
            'featured_image' => $this->storeImage('article-images/wifi.png'),
            'featured_image_alt' => 'Access point on an office ceiling',
        ]);
    }

    public function test_article_featured_image_shows_on_the_list_the_article_and_link_previews(): void
    {
        $article = $this->articleWithImage();
        $url = $article->featuredImageUrl();

        $this->assertNotNull($url);
        $this->get(route('articles.index'))->assertOk()->assertSee($url, false);
        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee('alt="Access point on an office ceiling"', false)
            ->assertSee('<meta property="og:image" content="'.e($url).'">', false)
            ->assertSee('summary_large_image', false);
        $this->get($url)->assertOk();
    }

    public function test_articles_without_an_image_still_render(): void
    {
        $article = Article::factory()->published()->create();

        $this->assertNull($article->featuredImageUrl());
        $this->get(route('articles.show', $article->slug))->assertOk()->assertDontSee('/media/articles/', false);
    }

    public function test_draft_article_images_are_not_served(): void
    {
        $draft = Article::factory()->create(['featured_image' => $this->storeImage('article-images/draft.png')]);

        $this->get(route('article-images.show', ['record' => $draft->id]))->assertNotFound();
    }

    public function test_replacing_or_deleting_an_article_image_removes_the_old_file(): void
    {
        $article = $this->articleWithImage();
        $new = $this->storeImage('article-images/new.png');

        $article->update(['featured_image' => $new]);
        Storage::disk('public')->assertMissing('article-images/wifi.png');

        $article->delete();
        Storage::disk('public')->assertMissing($new);
    }

    public function test_api_includes_the_article_image(): void
    {
        $article = $this->articleWithImage();

        $this->getJson('/api/v1/articles/'.$article->slug)
            ->assertOk()
            ->assertJsonPath('data.image_url', $article->featuredImageUrl())
            ->assertJsonPath('data.image_alt', 'Access point on an office ceiling');
    }

    public function test_service_featured_image_shows_on_cards_and_in_the_service_header(): void
    {
        $service = Service::factory()->create([
            'name' => 'Network audit',
            'is_active' => true,
            'sample_image' => $this->storeImage('service-samples/audit.png'),
        ]);
        $url = $service->sampleImageUrl();

        $this->get(route('services.index'))->assertOk()->assertSee($url, false);
        $this->get(route('home'))->assertOk()->assertSee($url, false);
        $this->get(route('services.show', $service->slug))
            ->assertOk()
            ->assertSee('alt="Network audit"', false)
            ->assertSee('<meta property="og:image" content="'.e($url).'">', false);
    }

    public function test_admin_can_upload_an_article_featured_image(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $article = Article::factory()->published()->create();

        Livewire::test(ManageArticles::class)
            ->mountTableAction('edit', $article)
            ->setTableActionData([
                'featured_image' => [UploadedFile::fake()->image('cover.jpg', 2400, 1200)],
                'featured_image_alt' => 'Server room',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $article->refresh();
        $this->assertStringStartsWith('article-images/', $article->featured_image);
        $this->assertSame('Server room', $article->featured_image_alt);
        Storage::disk('public')->assertExists($article->featured_image);
        // Large uploads are scaled down so pages stay quick to load.
        [$width] = getimagesize(Storage::disk('public')->path($article->featured_image));
        $this->assertLessThanOrEqual(1600, $width);
    }
}
