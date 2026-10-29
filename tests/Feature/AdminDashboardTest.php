<?php

namespace Tests\Feature;

use App\Filament\Widgets\AdminOverview;
use App\Models\Enquiry;
use App\Models\Project;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_includes_the_overview_widget(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/admin')
            ->assertOk()
            ->assertSeeLivewire(AdminOverview::class);
    }

    public function test_overview_shows_work_waiting_and_content_counts(): void
    {
        Enquiry::factory()->count(2)->create();
        SupportTicket::factory()->create();
        Project::factory()->published()->create();
        Project::factory()->create();

        $this->actingAs(User::factory()->create(['is_admin' => true]));

        Livewire::test(AdminOverview::class)
            ->assertSeeInOrder(['New enquiries', '2', 'Waiting for a response'])
            ->assertSee('Tickets waiting on you')
            ->assertSeeInOrder(['Published projects', '1', '2 in total, including drafts'])
            ->assertSee('Published articles');
    }
}
