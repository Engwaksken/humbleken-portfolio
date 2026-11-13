<?php

namespace App\Models;

use App\Enums\HeroImagePosition;
use App\Models\Concerns\Publishable;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

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
        'image_position',
    ];

    protected function casts(): array
    {
        return ['image_position' => HeroImagePosition::class];
    }

    /** Where the featured image goes; articles default to filling the page header behind the title. */
    public function featuredImagePosition(): HeroImagePosition
    {
        return $this->image_position ?? HeroImagePosition::Background;
    }

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

    /**
     * The body as HTML with an anchor on every section heading (h2), plus the list of those
     * headings for an "On this page" table of contents.
     *
     * @return array{html: HtmlString, toc: list<array{id: string, text: string}>}
     */
    public function bodyWithContents(): array
    {
        $toc = [];
        $used = [];

        $html = preg_replace_callback('#<h2>(.*?)</h2>#s', function (array $match) use (&$toc, &$used): string {
            $text = trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5));
            $id = Str::slug($text) ?: 'section';

            // Keep ids unique when two headings share the same text.
            $base = $id;
            for ($i = 2; isset($used[$id]); $i++) {
                $id = $base.'-'.$i;
            }
            $used[$id] = true;
            $toc[] = ['id' => $id, 'text' => $text];

            return '<h2 id="'.e($id).'">'.$match[1].'</h2>';
        }, (string) $this->bodyHtml()) ?? (string) $this->bodyHtml();

        return ['html' => new HtmlString($html), 'toc' => $toc];
    }

    /**
     * Other published articles on the same topic: same category or shared tags first.
     *
     * @return Collection<int, Article>
     */
    public function related(int $limit = 3): Collection
    {
        $tagIds = $this->tags->pluck('id');

        return static::query()
            ->published()
            ->whereKeyNot($this->getKey())
            ->where(function (Builder $query) use ($tagIds): void {
                $query->when($this->article_category_id, fn (Builder $query) => $query->where('article_category_id', $this->article_category_id))
                    ->orWhereHas('tags', fn (Builder $query) => $query->whereKey($tagIds));
            })
            ->with('category')
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
}
