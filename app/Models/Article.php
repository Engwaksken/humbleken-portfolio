<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    use Publishable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'article_category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'status',
        'published_at',
        'featured_image',
        'featured_image_alt',
    ];

    protected static function booted(): void
    {
        // Remove an image file once nothing points at it.
        static::updated(function (Article $article): void {
            $previous = $article->getOriginal('featured_image');

            if (filled($previous) && $previous !== $article->featured_image) {
                Storage::disk('public')->delete($previous);
            }
        });

        static::deleted(function (Article $article): void {
            if (filled($article->featured_image)) {
                Storage::disk('public')->delete($article->featured_image);
            }
        });
    }

    /**
     * Public URL of the featured image, or null when none is set or the file is missing.
     */
    public function featuredImageUrl(): ?string
    {
        $path = $this->featured_image;

        if (! is_string($path) || $path === '' || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return route('article-images.show', ['record' => $this->getKey(), 'v' => substr(md5($path), 0, 8)]);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Case-insensitive keyword match on title, excerpt and body.
     */
    #[Scope]
    protected function search(Builder $query, string $term): void
    {
        // Wildcards typed by visitors only widen the match; escaping them is not portable to SQLite.
        $pattern = '%'.trim($term).'%';

        $query->where(function (Builder $query) use ($pattern): void {
            $query->whereLike('title', $pattern)
                ->orWhereLike('excerpt', $pattern)
                ->orWhereLike('body', $pattern);
        });
    }

    /**
     * Rough reading time at 200 words per minute.
     */
    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->body ?? '')) / 200));
    }
}
