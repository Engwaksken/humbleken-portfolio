<?php

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class ProjectResource extends JsonResource
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
            'summary' => $this->summary,
            'technologies' => array_values(array_filter((array) $this->technologies, 'is_string')),
            'image_url' => $this->sampleImageUrl(),
            'published_at' => $this->published_at?->toIso8601String(),
            $this->mergeWhen($this->detailed, fn (): array => [
                'body_markdown' => (string) $this->body,
                'body_html' => (string) $this->bodyHtml(),
                'video_embed_url' => $this->sampleVideoEmbedUrl(),
            ]),
        ];
    }
}
