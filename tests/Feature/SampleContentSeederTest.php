<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Service;
use Database\Seeders\SampleContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SampleContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_sample_services_and_articles_with_featured_images(): void
    {
        $this->withoutVite();
        Storage::fake('public');

        $this->seed(SampleContentSeeder::class);

        $this->assertSame(count(SampleContentSeeder::SERVICES), Service::count());
        $this->assertSame(count(SampleContentSeeder::ARTICLES), Article::query()->published()->count());

        $service = Service::where('slug', 'office-network-setup')->sole();
        $this->assertSame('network-design-installation', $service->category->slug);
        $this->assertNotNull($service->sampleImageUrl());

        $article = Article::where('slug', 'the-3-2-1-backup-rule-explained-for-small-businesses')->sole();
        $this->assertSame('cloud-infrastructure', $article->category->slug);
        $this->assertEqualsCanonicalizing(['backups', 'disaster-recovery', 'data-protection', 'small-business'], $article->tags->pluck('slug')->all());
        $this->assertNotNull($article->featuredImageUrl());
        [$width, $height] = getimagesize(Storage::disk('public')->path($article->featured_image));
        $this->assertSame([1600, 900], [$width, $height]);

        $this->get(route('articles.index'))->assertOk()->assertSee('Five signs your office network needs attention');
        $this->get(route('services.index'))->assertOk()->assertSee('Office Network Setup');
    }

    public function test_running_again_keeps_edits_and_does_not_duplicate(): void
    {
        Storage::fake('public');
        $this->seed(SampleContentSeeder::class);

        $article = Article::orderBy('id')->first();
        $article->update(['title' => 'Edited title']);
        $image = $article->featured_image;

        $this->seed(SampleContentSeeder::class);

        $this->assertSame(count(SampleContentSeeder::ARTICLES), Article::count());
        $this->assertSame(count(SampleContentSeeder::SERVICES), Service::count());
        $this->assertSame(['Edited title', $image], [$article->fresh()->title, $article->fresh()->featured_image]);
    }

    public function test_sample_text_has_no_em_or_en_dashes(): void
    {
        $text = json_encode([SampleContentSeeder::SERVICES, SampleContentSeeder::ARTICLES], JSON_UNESCAPED_UNICODE);

        $this->assertStringNotContainsString('—', $text);
        $this->assertStringNotContainsString('–', $text);
    }
}
