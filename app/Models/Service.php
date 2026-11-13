<?php

namespace App\Models;

use App\Enums\HeroImagePosition;
use App\Models\Concerns\HasSampleMedia;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    use HasSampleMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'service_category_id',
        'name',
        'slug',
        'summary',
        'description',
        'delivery_method',
        'estimated_duration',
        'indicative_price',
        'is_active',
        'sort_order',
        'sample_image',
        'sample_video_url',
        'image_position',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'indicative_price' => 'decimal:2',
            'image_position' => HeroImagePosition::class,
        ];
    }

    /** Where the featured image goes; services default to beside the title. */
    public function featuredImagePosition(): HeroImagePosition
    {
        return $this->image_position ?? HeroImagePosition::Right;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
}
