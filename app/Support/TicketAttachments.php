<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Validation and private storage for files customers attach to support tickets.
 */
class TicketAttachments
{
    /**
     * @return array<string, list<string>>
     */
    public static function rules(): array
    {
        return [
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,png,jpg,jpeg,gif,txt,csv,doc,docx,xls,xlsx,zip'],
        ];
    }

    /**
     * Store the request's attachments on the private disk, compressing images to WebP.
     *
     * @return list<array{path: string, name: string}>
     */
    public static function store(Request $request): array
    {
        return collect($request->file('attachments', []))->map(function (UploadedFile $file): array {
            $compressed = ImageCompressor::compress($file->getRealPath(), (string) $file->getMimeType());
            $originalName = $file->getClientOriginalName();

            if ($compressed === null) {
                return ['path' => $file->store('ticket-attachments', 'local'), 'name' => $originalName];
            }

            $path = 'ticket-attachments/'.Str::uuid().'.webp';
            Storage::disk('local')->put($path, $compressed);

            return ['path' => $path, 'name' => pathinfo($originalName, PATHINFO_FILENAME).'.webp'];
        })->values()->all();
    }
}
