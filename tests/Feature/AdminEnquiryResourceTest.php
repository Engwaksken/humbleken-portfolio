<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEnquiryResourceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_list_and_view_enquiries(): void
    {
        $enquiry = Enquiry::factory()->create(['name' => 'Listed Person', 'message' => 'Printer keeps jamming on Mondays.']);

        $this->actingAs($this->admin())
            ->get('/admin/enquiries')
            ->assertOk()
            ->assertSee('Listed Person');

        $this->actingAs($this->admin())
            ->get("/admin/enquiries/{$enquiry->id}")
            ->assertOk()
            ->assertSee('Printer keeps jamming on Mondays.');
    }

    public function test_admin_cannot_create_enquiries_from_the_panel(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/enquiries/create')
            ->assertNotFound();
    }

    public function test_non_admin_cannot_view_enquiries(): void
    {
        $enquiry = Enquiry::factory()->create();
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin/enquiries')->assertForbidden();
        $this->actingAs($user)->get("/admin/enquiries/{$enquiry->id}")->assertForbidden();
    }

    public function test_guest_is_redirected_from_enquiries(): void
    {
        $this->get('/admin/enquiries')->assertRedirect('/admin/login');
    }
}
