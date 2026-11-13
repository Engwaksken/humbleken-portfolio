<?php

namespace App\Http\Resources;

use App\Models\Article;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Article */
class ArticleResource extends JsonResource
{
    public bool $detailed = false;

    public function detailed(): static
    {
        $this->detailed = true;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'category' => $this->category ? ['name' => $this->category->name, 'slug' => $this->category->slug] : null,
            'tags' => $this->tags->map(fn (Tag $tag): array => ['name' => $tag->name, 'slug' => $tag->slug])->values(),
            'published_at' => $this->published_at?->toIso8601String(),
            'reading_minutes' => $this->readingMinutes(),
            'image_url' => $this->featuredImageUrl(),
            'image_alt' => $this->featuredImageUrl() ? (string) $this->featured_image_alt : null,
            $this->mergeWhen($this->detailed, fn (): array => [
                'body_markdown' => (string) $this->body,
                'body_html' => (string) $this->bodyHtml(),
            ]),
        ];
    }
}
