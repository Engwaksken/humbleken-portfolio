<?php

namespace Tests\Feature;

use App\Filament\Pages\Settings as SettingsPage;
use App\Models\BookingRequest;
use App\Models\Enquiry;
use App\Models\SupportTicket;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class PrivacyAndAccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_privacy_page_shows_the_default_policy(): void
    {
        $this->get('/privacy')
            ->assertOk()
            ->assertSee('Privacy policy.')
            ->assertSee('Information I collect')
            ->assertSee('/account/delete');
    }

    public function test_admin_can_replace_the_policy_with_html_and_scripts_are_stripped(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(SettingsPage::class)
            ->set('data.privacy_policy', "## My own policy\n\n<strong>I keep things simple.</strong><script>alert(1)</script>")
            ->call('save')
            ->assertHasNoErrors();

        $this->get('/privacy')
            ->assertSee('My own policy')
            ->assertSee('<strong>I keep things simple.</strong>', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('Last updated');

        $this->getJson('/api/v1/privacy-policy')
            ->assertOk()
            ->assertJsonPath('data.url', route('privacy'))
            ->assertJsonFragment(['updated_at' => now()->toDateString()]);
    }

    public function test_delete_instructions_page_is_public_and_links_from_the_footer(): void
    {
        $this->get('/account/delete')->assertOk()->assertSee('Delete my account');
        $this->get('/')->assertSee(route('privacy'));
    }

    /**
     * @return array{User, SupportTicket, string}
     */
    private function customerWithData(): array
    {
        Storage::fake('local');
        $customer = User::factory()->create(['password' => 'secret-password']);
        $ticket = SupportTicket::factory()->for($customer)->create();
        Storage::disk('local')->put('ticket-attachments/file.webp', 'x');
        $ticket->replies()->make()->forceFill([
            'user_id' => $customer->id,
            'body' => 'Here is a screenshot.',
            'attachments' => [['path' => 'ticket-attachments/file.webp', 'name' => 'file.webp']],
        ])->save();
        BookingRequest::create(['name' => 'C', 'email' => $customer->email, 'preferred_at' => now()->addDay()])->forceFill(['user_id' => $customer->id])->save();
        Enquiry::factory()->create()->forceFill(['user_id' => $customer->id])->save();
        $customer->createToken('phone');

        return [$customer, $ticket, 'ticket-attachments/file.webp'];
    }

    private function assertCustomerDataGone(User $customer, string $attachment): void
    {
        $this->assertModelMissing($customer);
        $this->assertSame(0, SupportTicket::count());
        $this->assertSame(0, BookingRequest::count());
        $this->assertSame(0, Enquiry::count());
        $this->assertDatabaseCount('personal_access_tokens', 0);
        Storage::disk('local')->assertMissing($attachment);
    }

    public function test_customer_can_delete_their_account_on_the_website(): void
    {
        [$customer, , $attachment] = $this->customerWithData();
        $other = SupportTicket::factory()->create();

        $this->actingAs($customer)
            ->delete(route('account.destroy'), ['password' => 'wrong', 'confirm' => '1'])
            ->assertSessionHasErrors('password', null, 'deleteAccount');
        $this->assertModelExists($customer);

        $this->actingAs($customer)
            ->delete(route('account.destroy'), ['password' => 'secret-password', 'confirm' => '1'])
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $other->delete();
        $this->assertCustomerDataGone($customer, $attachment);
    }

    public function test_website_deletion_needs_the_confirmation_box(): void
    {
        $customer = User::factory()->create(['password' => 'secret-password']);

        $this->actingAs($customer)
            ->delete(route('account.destroy'), ['password' => 'secret-password'])
            ->assertSessionHasErrors('confirm', null, 'deleteAccount');
        $this->assertModelExists($customer);
    }

    public function test_customer_can_delete_their_account_from_the_app(): void
    {
        [$customer, , $attachment] = $this->customerWithData();
        Sanctum::actingAs($customer);

        $this->deleteJson('/api/v1/auth/account', ['password' => 'wrong'])->assertUnprocessable();

        $this->deleteJson('/api/v1/auth/account', ['password' => 'secret-password'])->assertNoContent();

        $this->assertCustomerDataGone($customer, $attachment);
    }

    public function test_administrators_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'password' => 'secret-password']);

        $this->actingAs($admin)
            ->delete(route('account.destroy'), ['password' => 'secret-password', 'confirm' => '1']);

        $this->assertModelExists($admin);
    }

    public function test_sitemap_lists_the_privacy_page(): void
    {
        Settings::set('brand_name', 'HumbleKen');

        $this->get('/sitemap.xml')->assertSee(route('privacy'));
    }
}
