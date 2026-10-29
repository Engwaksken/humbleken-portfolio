<?php

namespace App\Models\Concerns;

use App\Enums\PublicationStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Draft/published content with an optional scheduled publish date and a Markdown body.
 */
trait Publishable
{
    protected static function bootPublishable(): void
    {
        // Publishing without a date publishes immediately; a future date schedules it.
        static::saving(function (self $model): void {
            if ($model->status === PublicationStatus::Published && $model->published_at === null) {
                $model->published_at = now();
            }
        });
    }

    protected function initializePublishable(): void
    {
        $this->mergeCasts([
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
        ]);

        $this->attributes['status'] ??= PublicationStatus::Draft->value;
    }

    /**
     * Only records that are published and whose publish date has arrived.
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', PublicationStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->status === PublicationStatus::Published
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    /**
     * Render the Markdown body with raw HTML stripped and unsafe links removed.
     */
    public function bodyHtml(): HtmlString
    {
        return new HtmlString(Str::markdown($this->body ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]));
    }
}
