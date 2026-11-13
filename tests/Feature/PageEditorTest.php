<?php

namespace Tests\Feature;

use App\Filament\Pages\SitePages;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use App\Support\PageContent;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PageEditorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    private function storeImage(string $name = 'photo.png'): string
    {
        $path = PageContent::MEDIA_DIRECTORY.'/'.pathinfo($name, PATHINFO_FILENAME).'.png';
        Storage::disk('public')->put($path, UploadedFile::fake()->image($name, 40, 30)->getContent());

        return $path;
    }

    public function test_home_page_shows_default_sections(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Engineering solutions for reliable, effective IT.')
            ->assertSee('Start with the challenge')
            ->assertSee('Why work with us')
            ->assertSee('Have a technical challenge in mind?');
    }

    public function test_every_home_section_is_editable_and_can_be_hidden(): void
    {
        Service::factory()->create(['name' => 'Featured service']);
        Project::factory()->published()->create(['title' => 'Featured project']);

        PageContent::save('home', [
            'hero_eyebrow' => 'Hello there',
            'hero_title' => 'Custom hero title',
            'hero_intro' => 'Custom intro',
            'hero_primary_label' => 'See what we do',
            'hero_secondary_label' => 'Talk to us',
            'hero_aside' => 'card',
            'card_eyebrow' => 'Custom card',
            'card_text' => 'Card text',
            'card_steps' => [['text' => 'First custom step']],
            'card_link_label' => 'Write to us',
            'services_enabled' => true,
            'services_eyebrow' => 'Offer',
            'services_heading' => 'Custom services heading',
            'services_link_label' => 'Every service',
            'services_count' => 1,
            'why_enabled' => true,
            'why_eyebrow' => 'Reasons',
            'why_heading' => 'Custom why heading',
            'why_items' => [['icon' => 'fa-bolt', 'heading' => 'Fast fixes', 'text' => 'Quick turnaround.', 'image' => null]],
            'projects_enabled' => false,
            'cta_enabled' => true,
            'cta_title' => 'Custom CTA title',
            'cta_text' => 'Custom CTA text',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Hello there', 'Custom hero title', 'Custom intro', 'See what we do', 'Talk to us'])
            ->assertSeeInOrder(['Custom card', 'First custom step', 'Write to us'])
            ->assertSeeInOrder(['Offer', 'Custom services heading', 'Every service', 'Featured service'])
            ->assertSeeInOrder(['Reasons', 'Custom why heading', 'fa-bolt', 'Fast fixes'])
            ->assertSee('Custom CTA title')
            ->assertDontSee('Featured project')
            ->assertDontSee('Engineering solutions for reliable, effective IT.')
            ->assertDontSee('Clear communication');
    }

    public function test_hero_image_replaces_the_card(): void
    {
        $path = $this->storeImage('hero.png');
        PageContent::save('home', array_merge(PageContent::raw('home'), [
            'hero_aside' => 'image',
            'hero_image' => $path,
            'hero_image_alt' => 'Engineer at a server rack',
        ]));

        $this->get('/')
            ->assertOk()
            ->assertSee(route('page-media.show', ['file' => 'hero.png']), false)
            ->assertSee('alt="Engineer at a server rack"', false)
            ->assertDontSee('Start with the challenge');

        $this->get(route('page-media.show', ['file' => 'hero.png']))->assertOk();
    }

    public function test_hero_image_can_sit_left_right_or_behind_the_text(): void
    {
        $path = $this->storeImage('hero.png');
        $save = fn (string $position) => PageContent::save('home', array_merge(PageContent::raw('home'), [
            'hero_aside' => 'image',
            'hero_image' => $path,
            'hero_image_alt' => 'Server rack',
            'hero_image_position' => $position,
        ]));

        $save('left');
        $this->get('/')->assertOk()->assertSee('lg:order-last', false)->assertSee('alt="Server rack"', false);

        $save('right');
        $this->get('/')->assertOk()->assertDontSee('lg:order-last', false)->assertDontSee('hero-on-image', false);

        // As a background the image is decorative, and the text sits on a darkened overlay.
        $save('background');
        $this->get('/')->assertOk()
            ->assertSee('hero-on-image', false)
            ->assertSee(route('page-media.show', ['file' => 'hero.png']), false)
            ->assertDontSee('alt="Server rack"', false);

        $save('somewhere-else');
        $this->assertSame('right', PageContent::get('home')['hero']['image_position']);
    }

    public function test_hero_intro_gets_a_read_more_toggle_unless_turned_off(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('data-read-more style="--read-more-lines: 2"', false)
            ->assertSee('Read more');

        PageContent::save('home', array_merge(PageContent::raw('home'), ['hero_intro_lines' => 0]));

        $this->get('/')->assertOk()->assertDontSee('data-read-more style', false)->assertDontSee('data-read-more-toggle', false);
    }

    public function test_other_page_headers_get_a_read_more_toggle_with_their_own_line_setting(): void
    {
        foreach (['services.index', 'approach', 'booking', 'contact', 'projects.index', 'articles.index'] as $route) {
            $this->get(route($route))->assertOk()
                ->assertSee('data-read-more style="--read-more-lines: 2"', false)
                ->assertSee('data-read-more-toggle', false);
        }

        PageContent::save('contact', array_merge(PageContent::raw('contact'), ['intro_lines' => 4]));
        $this->get(route('contact'))->assertSee('data-read-more style="--read-more-lines: 4"', false);

        PageContent::save('approach', array_merge(PageContent::raw('approach'), ['intro_lines' => 0]));
        $this->get(route('approach'))->assertDontSee('data-read-more-toggle', false);
    }

    public function test_image_aside_without_an_image_falls_back_to_text_only(): void
    {
        PageContent::save('home', array_merge(PageContent::raw('home'), ['hero_aside' => 'image', 'hero_image' => null]));

        $this->assertSame('none', PageContent::get('home')['hero']['aside']);
        $this->get('/')->assertOk()->assertDontSee('Start with the challenge');
    }

    public function test_pages_show_header_image_steps_body_and_gallery(): void
    {
        $header = $this->storeImage('header.png');
        $step = $this->storeImage('step.png');
        $gallery = $this->storeImage('gallery.png');

        PageContent::save('approach', [
            'eyebrow' => 'How we work',
            'title' => 'Our approach',
            'intro' => 'Intro text',
            'image' => $header,
            'image_alt' => 'Whiteboard planning session',
            'body' => 'Some extra words.',
            'steps' => [['title' => 'Listen', 'text' => 'We listen first.', 'image' => $step]],
            'gallery' => [['image' => $gallery, 'caption' => 'Workshop in progress']],
        ]);

        $this->get(route('approach'))
            ->assertOk()
            ->assertSeeInOrder(['How we work', 'Our approach', 'Intro text'])
            ->assertSee('alt="Whiteboard planning session"', false)
            ->assertSeeInOrder(['Listen', 'We listen first.'])
            ->assertSee(route('page-media.show', ['file' => 'step.png']), false)
            ->assertSee('Some extra words.')
            ->assertSee('Workshop in progress')
            ->assertSee(route('page-media.show', ['file' => 'gallery.png']), false);
    }

    public function test_legacy_single_field_settings_are_still_used(): void
    {
        Settings::set('home_title', 'Legacy home title');
        Settings::set('approach_steps', "Old step one\nOld step two");

        $this->get('/')->assertSee('Legacy home title');
        $this->get(route('approach'))->assertSeeInOrder(['Old step one', 'Old step two']);
    }

    public function test_page_media_route_only_serves_page_images(): void
    {
        Storage::disk('public')->put('branding/secret.png', 'x');

        $this->get('/media/pages/missing.png')->assertNotFound();
        $this->get('/media/pages/..%2Fbranding%2Fsecret.png')->assertNotFound();
    }

    public function test_admin_can_edit_pages_and_unused_images_are_removed(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $old = $this->storeImage('old.png');
        PageContent::save('contact', array_merge(PageContent::raw('contact'), ['image' => $old]));

        Livewire::test(SitePages::class)
            ->assertSee(['Hero', 'Why work with us', 'Recent work', 'Call to action'])
            ->set('data.home.hero_title', 'Edited in the admin')
            ->set('data.contact.title', 'Edited contact title')
            ->set('data.contact.image', null)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Edited in the admin', PageContent::get('home')['hero']['title']);
        $this->assertSame('Edited contact title', PageContent::get('contact')['title']);
        Storage::disk('public')->assertMissing($old);
        $this->get(route('contact'))->assertSee('Edited contact title');
    }

    public function test_admin_can_upload_page_and_gallery_images(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(SitePages::class)
            ->set('data.services.image', [UploadedFile::fake()->image('header.jpg', 2400, 300)])
            ->set('data.services.image_alt', 'Laptop on a desk')
            ->set('data.services.gallery', ['item-1' => ['image' => null, 'caption' => 'First photo']])
            ->set('data.services.gallery.item-1.image', [UploadedFile::fake()->image('one.png', 300, 200)])
            ->call('save')
            ->assertHasNoErrors();

        $page = PageContent::get('services');
        $raw = PageContent::raw('services');

        $this->assertNotNull($page['image_url']);
        $this->assertSame('Laptop on a desk', $page['image_alt']);
        $this->assertCount(1, $page['gallery']);
        $this->assertSame('First photo', $page['gallery'][0]['caption']);
        Storage::disk('public')->assertExists($raw['image']);

        if (extension_loaded('gd') && function_exists('imagewebp')) {
            $this->assertStringEndsWith('.webp', $raw['image']);
            [$width] = getimagesizefromstring(Storage::disk('public')->get($raw['image']));
            $this->assertLessThanOrEqual(2000, $width);
        }

        $this->get(route('services.index'))
            ->assertSee('alt="Laptop on a desk"', false)
            ->assertSee('First photo');
    }

    public function test_customers_cannot_open_the_page_editor(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]));

        $this->get(SitePages::getUrl())->assertForbidden();
    }
}
