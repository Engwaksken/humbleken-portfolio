<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

trait HasSampleMedia
{
    protected static function bootHasSampleMedia(): void
    {
        static::updated(function (Model $model): void {
            $previous = $model->getOriginal('sample_image');

            if (filled($previous) && $previous !== $model->sample_image) {
                Storage::disk('public')->delete($previous);
            }
        });

        static::deleted(function (Model $model): void {
            if (filled($model->sample_image)) {
                Storage::disk('public')->delete($model->sample_image);
            }
        });
    }

    public function sampleImageUrl(): ?string
    {
        $path = $this->sample_image;

        return is_string($path) && Storage::disk('public')->exists($path)
            ? Storage::disk('public')->url($path)
            : null;
    }

    public function sampleVideoEmbedUrl(): ?string
    {
        $url = $this->sample_video_url;

        if (! is_string($url) || ($parts = parse_url($url)) === false || strtolower($parts['scheme'] ?? '') !== 'https') {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            parse_str($parts['query'] ?? '', $query);
            $id = $query['v'] ?? null;
            $id ??= preg_match('#^(?:embed|shorts)/([\w-]{11})$#', $path, $matches) ? $matches[1] : null;

            return is_string($id) && preg_match('/^[\w-]{11}$/', $id)
                ? 'https://www.youtube-nocookie.com/embed/'.$id
                : null;
        }

        if ($host === 'youtu.be' && preg_match('/^[\w-]{11}$/', $path)) {
            return 'https://www.youtube-nocookie.com/embed/'.$path;
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com'], true) && ctype_digit($path)) {
            return 'https://player.vimeo.com/video/'.$path;
        }

        return null;
    }

    public static function isAllowedSampleVideoUrl(?string $url): bool
    {
        if (blank($url)) {
            return true;
        }

        return (new static(['sample_video_url' => $url]))->sampleVideoEmbedUrl() !== null;
    }
}
