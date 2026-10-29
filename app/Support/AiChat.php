<?php

namespace App\Support;

use App\Models\Article;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Throwable;

class AiChat
{
    public static function enabled(): bool
    {
        return (bool) Settings::get('ai_chat_enabled', false)
            && filled(Settings::get('ai_api_base_url'))
            && filled(Settings::get('ai_api_model'))
            && filled(static::apiKey());
    }

    /** @param array<int, array{role: string, content: string}> $messages */
    public static function reply(array $messages): string
    {
        $baseUrl = rtrim((string) Settings::get('ai_api_base_url'), '/');
        $url = str_ends_with($baseUrl, '/v1') ? $baseUrl.'/chat/completions' : $baseUrl.'/v1/chat/completions';
        $key = static::apiKey();
        abort_unless($key !== null, 503);

        $context = collect([
            ...Service::query()->where('is_active', true)->orderBy('sort_order')->get(['name', 'summary', 'description'])->map(fn ($service) => "Service: {$service->name}. ".($service->summary ?: $service->description))->all(),
            ...Project::query()->published()->ordered()->limit(10)->get(['title', 'summary'])->map(fn ($project) => "Project: {$project->title}. {$project->summary}")->all(),
            ...Article::query()->published()->latest('published_at')->limit(10)->get(['title', 'excerpt'])->map(fn ($article) => "Article: {$article->title}. {$article->excerpt}")->all(),
        ])->filter()->implode("\n");

        try {
            $response = Http::acceptJson()->withToken($key)->timeout(20)->post($url, [
                'model' => (string) Settings::get('ai_api_model'),
                'messages' => [
                    ['role' => 'system', 'content' => "You are the website's helpful assistant. Answer using the provided public website information. Do not invent details, prices, or commitments. If you cannot answer, invite the visitor to use the contact page. Keep answers concise.\n\nPublic website information:\n".$context],
                    ...$messages,
                ],
                'temperature' => 0.4,
                'max_tokens' => 700,
            ]);

            $response->throw();
            $answer = $response->json('choices.0.message.content');

            if (! is_string($answer) || trim($answer) === '') {
                throw new \RuntimeException('The AI provider returned an empty response.');
            }

            return trim($answer);
        } catch (Throwable $exception) {
            report($exception);
            throw new \RuntimeException('The assistant is temporarily unavailable. Please try again or use the contact page.', previous: $exception);
        }
    }

    public static function apiKey(): ?string
    {
        $encrypted = Settings::get('ai_api_key_ciphertext');

        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (Throwable) {
            report(new \RuntimeException('Unable to decrypt the configured AI API key.'));

            return null;
        }
    }
}
