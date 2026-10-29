<?php

namespace Tests\Feature;

use App\Filament\Resources\ServiceResource\Pages\ManageServices;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminServiceMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_a_sample_image_and_video_to_a_service(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(ManageServices::class)
            ->callAction('create', data: [
                'name' => 'Network assessment',
                'slug' => 'network-assessment',
                'is_active' => true,
                'sort_order' => 0,
                'sample_image' => UploadedFile::fake()->createWithContent('sample.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=')),
                'sample_video_url' => 'https://www.youtube.com/watch?v=abcdefghijk',
            ])
            ->assertHasNoActionErrors();

        $service = Service::query()->where('slug', 'network-assessment')->sole();
        $this->assertSame('https://www.youtube-nocookie.com/embed/abcdefghijk', $service->sampleVideoEmbedUrl());
        Storage::disk('public')->assertExists($service->sample_image);

        $this->get('/')
            ->assertSee('Sample image for Network assessment')
            ->assertSee('https://www.youtube-nocookie.com/embed/abcdefghijk');
    }

    public function test_untrusted_video_domains_are_not_embedded(): void
    {
        $service = Service::factory()->create(['sample_video_url' => 'https://attacker.example/video/1']);

        $this->assertNull($service->sampleVideoEmbedUrl());
    }
}
