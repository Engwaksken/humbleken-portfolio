<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\Article;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SupportTicket;
use App\Models\User;
use App\Support\PageContent;
use App\Support\Settings;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_site_endpoint_returns_brand_contact_and_features(): void
    {
        Settings::set('brand_name', 'Acme IT');
        Settings::set('contact_email', 'hello@example.test');
        Settings::set('social_links', ['linkedin' => 'https://linkedin.example.test/acme', 'bad' => 'javascript:alert(1)']);

        $this->getJson('/api/v1/site')
            ->assertOk()
            ->assertJsonPath('data.brand_name', 'Acme IT')
            ->assertJsonPath('data.contact.email', 'hello@example.test')
            ->assertJsonPath('data.social_links', [['platform' => 'linkedin', 'url' => 'https://linkedin.example.test/acme']])
            ->assertJsonPath('data.features.projects', false)
            ->assertJsonPath('data.maintenance.enabled', false)
            ->assertJsonStructure(['data' => ['theme' => ['primary', 'secondary'], 'logo_url']]);
    }

    public function test_home_page_endpoint_returns_every_section_with_featured_content(): void
    {
        Service::factory()->create(['name' => 'Active service']);
        Service::factory()->create(['name' => 'Hidden service', 'is_active' => false]);
        Project::factory()->published()->create(['title' => 'Live project']);
        Project::factory()->create(['title' => 'Draft project']);

        $this->getJson('/api/v1/pages/home')
            ->assertOk()
            ->assertJsonPath('data.hero.title', 'Engineering solutions for reliable, effective IT.')
            ->assertJsonPath('data.hero.aside', 'card')
            ->assertJsonCount(3, 'data.hero.card.steps')
            ->assertJsonCount(1, 'data.services.items')
            ->assertJsonPath('data.services.items.0.name', 'Active service')
            ->assertJsonCount(1, 'data.projects.items')
            ->assertJsonPath('data.projects.items.0.title', 'Live project')
            ->assertJsonPath('data.why.items.0.icon', 'fa-comments')
            ->assertJsonPath('data.cta.enabled', true);
    }

    public function test_disabled_home_sections_return_no_items(): void
    {
        Service::factory()->create();
        PageContent::save('home', array_merge(PageContent::raw('home'), ['services_enabled' => false]));

        $this->getJson('/api/v1/pages/home')
            ->assertJsonPath('data.services.enabled', false)
            ->assertJsonPath('data.services.items', []);
    }

    public function test_site_page_endpoint_returns_steps_and_unknown_pages_404(): void
    {
        $this->getJson('/api/v1/pages/approach')
            ->assertOk()
            ->assertJsonPath('data.key', 'approach')
            ->assertJsonCount(3, 'data.steps')
            ->assertJsonPath('data.gallery', []);

        $this->getJson('/api/v1/pages/booking')->assertJsonCount(3, 'data.steps');
        $this->getJson('/api/v1/pages/contact')->assertJsonPath('data.steps', []);
        $this->getJson('/api/v1/pages/settings')->assertNotFound();
    }

    public function test_services_projects_and_articles_only_expose_public_content(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Networks', 'is_active' => true]);
        $service = Service::factory()->create(['service_category_id' => $category->id, 'slug' => 'wifi', 'description' => 'Full description']);
        Service::factory()->create(['slug' => 'hidden', 'is_active' => false]);
        Project::factory()->published()->create(['slug' => 'live', 'body' => '## Result']);
        Project::factory()->create(['slug' => 'draft']);
        Article::factory()->published()->create(['slug' => 'how-to', 'title' => 'How to fix printers', 'body' => 'Restart it.']);
        Article::factory()->create(['slug' => 'secret']);

        $this->getJson('/api/v1/services')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category.name', 'Networks')
            ->assertJsonPath('categories.0.name', 'Networks');
        $this->getJson('/api/v1/services/wifi')->assertOk()->assertJsonPath('data.description', 'Full description')->assertJsonPath('data.id', $service->id);
        $this->getJson('/api/v1/services/hidden')->assertNotFound();

        $this->getJson('/api/v1/projects')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/projects/live')->assertOk()->assertJsonPath('data.body_markdown', '## Result')->assertJsonPath('data.body_html', "<h2>Result</h2>\n");
        $this->getJson('/api/v1/projects/draft')->assertNotFound();

        $this->getJson('/api/v1/articles?q=printers')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'how-to');
        $this->getJson('/api/v1/articles?q=nothing-matches')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/articles/how-to')->assertOk()->assertJsonPath('data.body_markdown', 'Restart it.');
        $this->getJson('/api/v1/articles/secret')->assertNotFound();
    }

    public function test_enquiries_and_booking_requests_can_be_submitted(): void
    {
        $this->postJson('/api/v1/enquiries', ['name' => 'Ann', 'email' => 'ann@example.test', 'message' => 'My network keeps dropping.'])
            ->assertCreated()
            ->assertJsonStructure(['message']);
        $this->assertDatabaseHas('enquiries', ['email' => 'ann@example.test']);

        $this->postJson('/api/v1/enquiries', ['name' => 'Ann'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'message']);

        $this->postJson('/api/v1/booking-requests', ['name' => 'Ben', 'email' => 'ben@example.test', 'preferred_at' => now()->addDay()->toIso8601String()])
            ->assertCreated();
        $this->assertDatabaseHas('booking_requests', ['email' => 'ben@example.test']);

        $this->postJson('/api/v1/booking-requests', ['name' => 'Ben', 'email' => 'ben@example.test', 'preferred_at' => now()->subDay()->toIso8601String()])
            ->assertJsonValidationErrors(['preferred_at']);
    }

    public function test_errors_are_json_even_without_an_accept_header(): void
    {
        $this->post('/api/v1/enquiries', [])->assertStatus(422)->assertJsonValidationErrors(['name']);
        $this->get('/api/v1/tickets')->assertUnauthorized()->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_customer_can_register_log_in_and_log_out(): void
    {
        Notification::fake();

        $register = $this->postJson('/api/v1/auth/register', [
            'name' => 'Cara',
            'email' => 'cara@example.test',
            'password' => 'a-Strong-passw0rd!',
            'password_confirmation' => 'a-Strong-passw0rd!',
            'device_name' => 'Pixel 9',
        ])->assertCreated()->assertJsonPath('user.email_verified', false);

        $this->assertNotEmpty($register->json('token'));
        Notification::assertSentTo(User::firstWhere('email', 'cara@example.test'), VerifyEmail::class);

        $this->postJson('/api/v1/auth/login', ['email' => 'cara@example.test', 'password' => 'wrong', 'device_name' => 'Pixel'])
            ->assertJsonValidationErrors(['email']);

        $token = $this->postJson('/api/v1/auth/login', ['email' => 'cara@example.test', 'password' => 'a-Strong-passw0rd!', 'device_name' => 'Pixel'])
            ->assertOk()
            ->json('token');

        $this->withToken($token)->getJson('/api/v1/auth/user')->assertOk()->assertJsonPath('data.email', 'cara@example.test');
        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_tokens_expire(): void
    {
        $user = User::factory()->create();
        $token = $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password', 'device_name' => 'Phone'])->json('token');

        $this->travel(31)->days();

        $this->withToken($token)->getJson('/api/v1/auth/user')->assertUnauthorized();
    }

    public function test_administrators_cannot_get_api_tokens(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->postJson('/api/v1/auth/login', ['email' => $admin->email, 'password' => 'password', 'device_name' => 'Phone'])
            ->assertJsonValidationErrors(['email']);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_unverified_customers_cannot_use_tickets(): void
    {
        Sanctum::actingAs(User::factory()->unverified()->create());

        $this->getJson('/api/v1/tickets')
            ->assertForbidden()
            ->assertJson(['message' => 'Your email address is not verified.']);
    }

    public function test_customer_can_open_view_and_reply_to_their_own_tickets(): void
    {
        Notification::fake();
        $customer = User::factory()->create();
        Sanctum::actingAs($customer);

        $ticketId = $this->postJson('/api/v1/tickets', ['subject' => 'Printer offline', 'body' => 'The office printer is offline again.'])
            ->assertCreated()
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.replies.0.body', 'The office printer is offline again.')
            ->json('data.id');

        $ticket = SupportTicket::find($ticketId);
        $staff = User::factory()->create(['is_admin' => true]);
        $ticket->addReply($staff, 'Please power-cycle it.');
        $ticket->addReply($staff, 'Internal: customer is on old firmware.', internal: true);

        $this->getJson('/api/v1/tickets')->assertOk()->assertJsonPath('data.0.status', 'answered')->assertJsonMissingPath('data.0.replies');

        $this->getJson("/api/v1/tickets/{$ticketId}")
            ->assertOk()
            ->assertJsonCount(2, 'data.replies')
            ->assertJsonPath('data.replies.1.from_staff', true)
            ->assertJsonPath('data.replies.1.author_name', 'Support team')
            ->assertDontSee('old firmware');

        $this->postJson("/api/v1/tickets/{$ticketId}/replies", ['body' => 'Done, works now.'])
            ->assertCreated()
            ->assertJsonPath('data.status', 'open')
            ->assertJsonCount(3, 'data.replies');

        $ticket->forceFill(['status' => TicketStatus::Closed])->save();
        $this->postJson("/api/v1/tickets/{$ticketId}/replies", ['body' => 'One more thing'])->assertForbidden();
    }

    public function test_customers_cannot_see_other_customers_tickets(): void
    {
        $other = SupportTicket::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/v1/tickets/{$other->id}")->assertNotFound();
        $this->postJson("/api/v1/tickets/{$other->id}/replies", ['body' => 'Hello there'])->assertNotFound();
        $this->getJson('/api/v1/tickets')->assertOk()->assertJsonCount(0, 'data');
    }
}
