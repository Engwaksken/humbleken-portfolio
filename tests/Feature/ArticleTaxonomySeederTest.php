<?php

namespace Tests\Feature;

use App\Models\ArticleCategory;
use App\Models\Tag;
use Database\Seeders\ArticleTaxonomySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTaxonomySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_sample_categories_and_tags_and_can_run_again(): void
    {
        $this->seed(ArticleTaxonomySeeder::class);
        ArticleCategory::where('slug', 'networking')->update(['description' => 'Edited in the admin']);
        $this->seed(ArticleTaxonomySeeder::class);

        $this->assertSame(count(ArticleTaxonomySeeder::CATEGORIES), ArticleCategory::count());
        $this->assertSame(count(ArticleTaxonomySeeder::TAGS), Tag::count());
        $this->assertSame('Edited in the admin', ArticleCategory::where('slug', 'networking')->value('description'));
        $this->assertTrue(Tag::where('slug', 'wi-fi')->exists());
    }
}
