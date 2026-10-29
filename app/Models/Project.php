<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'body',
        'technologies',
        'status',
        'published_at',
        'sort_order',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'published_at' => 'datetime',
            'technologies' => 'array',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Publishing without a date publishes immediately; a future date schedules it.
        static::saving(function (Project $project): void {
            if ($project->status === ProjectStatus::Published && $project->published_at === null) {
                $project->published_at = now();
            }
        });
    }

    /**
     * Only projects that are published and whose publish date has arrived.
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', ProjectStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderByDesc('published_at');
    }

    public function isPublished(): bool
    {
        return $this->status === ProjectStatus::Published
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
