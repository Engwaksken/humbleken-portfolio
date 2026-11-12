<?php

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Service */
class ServiceResource extends JsonResource
{
    /** Include the full description and delivery details. */
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
        $category = $this->category?->is_active ? $this->category : null;

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'summary' => $this->summary ?: $this->description,
            'category' => $category ? ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug] : null,
            'image_url' => $this->sampleImageUrl(),
            $this->mergeWhen($this->detailed, fn (): array => [
                'description' => $this->description,
                'delivery_method' => $this->delivery_method,
                'estimated_duration' => $this->estimated_duration,
                'indicative_price' => $this->indicative_price,
                'video_embed_url' => $this->sampleVideoEmbedUrl(),
            ]),
        ];
    }
}
