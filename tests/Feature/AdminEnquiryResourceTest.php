<?php

namespace Tests\Feature;

use App\Enums\EnquiryStatus;
use App\Filament\Resources\EnquiryResource;
use App\Filament\Resources\EnquiryResource\Pages\ManageEnquiries;
use App\Models\Enquiry;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminEnquiryResourceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_list_enquiries(): void
    {
        Enquiry::factory()->create(['name' => 'Listed Person']);

        $this->actingAs($this->admin())
            ->get('/admin/enquiries')
            ->assertOk()
            ->assertSee('Listed Person');
    }

    public function test_admin_can_view_an_enquiry_in_a_modal(): void
    {
        $enquiry = Enquiry::factory()->create(['message' => 'Printer keeps jamming on Mondays.']);

        $this->actingAs($this->admin());
        Livewire::test(ManageEnquiries::class)
            ->mountAction(TestAction::make('view')->table($enquiry))
            ->assertMountedActionModalSee('Printer keeps jamming on Mondays.');
    }

    public function test_admin_can_update_status_in_a_modal(): void
    {
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($this->admin());
        Livewire::test(ManageEnquiries::class)
            ->callAction(TestAction::make('edit')->table($enquiry), data: ['status' => EnquiryStatus::Closed->value])
            ->assertHasNoActionErrors();

        $this->assertSame(EnquiryStatus::Closed, $enquiry->fresh()->status);
    }

    public function test_admin_can_delete_an_enquiry_after_confirming(): void
    {
        $enquiry = Enquiry::factory()->create();

        $this->actingAs($this->admin());
        Livewire::test(ManageEnquiries::class)
            ->callAction(TestAction::make('delete')->table($enquiry));

        $this->assertModelMissing($enquiry);
    }

    public function test_enquiries_cannot_be_created_from_the_panel(): void
    {
        $this->actingAs($this->admin());
        Livewire::test(ManageEnquiries::class)->assertActionDoesNotExist('create');
    }

    public function test_notification_link_opens_the_enquiry_modal(): void
    {
        $enquiry = Enquiry::factory()->create();

        $url = EnquiryResource::getRecordUrl($enquiry);

        $this->assertStringContainsString('tableAction=view', $url);
        $this->assertStringContainsString("tableActionRecord={$enquiry->id}", $url);
        $this->actingAs($this->admin())->get($url)->assertOk();
    }

    public function test_non_admin_cannot_view_enquiries(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin/enquiries')->assertForbidden();
    }

    public function test_guest_is_redirected_from_enquiries(): void
    {
        $this->get('/admin/enquiries')->assertRedirect('/admin/login');
    }
}
