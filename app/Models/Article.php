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
    ];

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
