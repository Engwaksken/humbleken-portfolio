<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Service;
use App\Support\Settings;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMediaController extends Controller
{
    public function branding(string $key): StreamedResponse
    {
        abort_unless(in_array($key, ['logo', 'favicon'], true), 404);

        $path = Settings::get($key);
        abort_unless(is_string($path) && $path !== '' && Storage::disk('public')->exists($path), 404);

        return $this->inlineFile($path);
    }

    public function sample(string $type, int $record): StreamedResponse
    {
        $model = match ($type) {
            'project' => Project::query()->published()->findOrFail($record),
            'service' => Service::query()->where('is_active', true)->findOrFail($record),
            default => abort(404),
        };

        $path = $model->sample_image;
        abort_unless(is_string($path) && $path !== '' && Storage::disk('public')->exists($path), 404);

        return $this->inlineFile($path);
    }

    private function inlineFile(string $path): StreamedResponse
    {
        $disk = Storage::disk('public');

        return $disk->response($path, basename($path), [
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=3600',
        ], 'inline');
    }
}
