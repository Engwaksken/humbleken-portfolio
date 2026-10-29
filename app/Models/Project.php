<?php

namespace App\Models;

use App\Models\Concerns\HasSampleMedia;
use App\Models\Concerns\Publishable;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    use HasSampleMedia;
    use Publishable;

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
        'sample_image',
        'sample_video_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'sort_order' => 'integer',
        ];
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderByDesc('published_at');
    }
}
