<?php

namespace Tests\Feature;

use App\Filament\Pages\Backups as BackupsPage;
use App\Models\User;
use App\Support\Backups;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

/**
 * Uses DatabaseMigrations rather than RefreshDatabase because SQLite cannot
 * take a VACUUM INTO snapshot inside the test transaction.
 */
class AdminBackupsTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        // Backups read the real uploads folder, so remove the sample upload written below.
        File::delete(storage_path('app/public/branding/backup-test-logo.png'));

        parent::tearDown();
    }

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_admin_can_create_a_backup_containing_the_database_and_uploads(): void
    {
        File::ensureDirectoryExists(storage_path('app/public/branding'));
        File::put(storage_path('app/public/branding/backup-test-logo.png'), 'png-bytes');
        $this->actingAsAdmin();

        Livewire::test(BackupsPage::class)
            ->callAction('create')
            ->assertNotified('Backup created');

        $backup = Backups::all()->sole();
        $zip = new ZipArchive;
        $this->assertTrue($zip->open(Storage::disk('local')->path($backup['path'])) === true);
        // Normalise separators: on Windows the archive paths can mix "\" and "/".
        $names = collect(range(0, $zip->numFiles - 1))->map(fn (int $i): string => str_replace('\\', '/', $zip->getNameIndex($i)));
        $zip->close();

        $this->assertTrue($names->contains(fn (string $name): bool => str_ends_with($name, 'database.sqlite')));
        $this->assertTrue($names->contains(fn (string $name): bool => str_ends_with($name, 'branding/backup-test-logo.png')));
        $this->assertFalse($names->contains(fn (string $name): bool => str_ends_with($name, '.env')));
    }

    public function test_backups_are_listed_and_can_be_downloaded_and_deleted(): void
    {
        Storage::disk('local')->put('site-backups/2026-01-01-00-00-00.zip', 'zip-bytes');
        $this->actingAsAdmin();

        Livewire::test(BackupsPage::class)
            ->assertSee('2026-01-01-00-00-00.zip')
            ->callAction(TestAction::make('download')->table('2026-01-01-00-00-00.zip'))
            ->assertFileDownloaded('2026-01-01-00-00-00.zip')
            ->callAction(TestAction::make('delete')->table('2026-01-01-00-00-00.zip'));

        Storage::disk('local')->assertMissing('site-backups/2026-01-01-00-00-00.zip');
    }

    public function test_backup_paths_cannot_escape_the_backup_folder(): void
    {
        $this->expectException(RuntimeException::class);

        Backups::pathFor('../../.env');
    }

    public function test_non_admins_cannot_open_backups(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/backups')->assertForbidden();
        auth()->logout();
        $this->get('/admin/backups')->assertRedirect('/admin/login');
    }

    public function test_admin_can_open_the_backups_page(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin/backups')->assertOk()->assertSee('No backups yet');
    }
}
