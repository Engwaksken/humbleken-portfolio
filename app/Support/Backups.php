<?php

namespace App\Support;

use App\Support\Settings as SettingsStore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Creates, lists and removes site backups (database plus uploaded files).
 */
class Backups
{
    public static function disk(): string
    {
        return 'local';
    }

    /** @return list<string> */
    public static function disks(): array
    {
        $disks = [static::disk()];
        $remote = (string) SettingsStore::get('backup_remote_disk', 's3');

        if ((bool) SettingsStore::get('backup_remote_enabled', false) && in_array($remote, ['s3'], true)) {
            $disks[] = $remote;
        }

        return $disks;
    }

    public static function directory(): string
    {
        return config('backup.backup.name');
    }

    /**
     * @return Collection<int, array{path: string, name: string, size: int, created_at: int}>
     */
    public static function all(): Collection
    {
        return collect(static::disks())->flatMap(function (string $diskName): array {
            try {
                $disk = Storage::disk($diskName);

                return collect($disk->files(static::directory()))
                    ->filter(fn (string $path): bool => str_ends_with($path, '.zip'))
                    ->map(fn (string $path): array => [
                        'disk' => $diskName,
                        'path' => $path,
                        'name' => basename($path),
                        'size' => $disk->size($path),
                        'created_at' => $disk->lastModified($path),
                    ])->all();
            } catch (\Throwable $exception) {
                report($exception);

                return [];
            }
        })
            ->sortByDesc('created_at')
            ->values();
    }

    public static function create(): void
    {
        $connection = config('database.default');
        $snapshotDir = storage_path('app/backup-snapshot');

        // The folder is listed in config/backup.php, so it must exist even when empty.
        File::ensureDirectoryExists($snapshotDir);

        try {
            if (config("database.connections.{$connection}.driver") !== 'sqlite') {
                // PostgreSQL/MySQL dumps use the host's pg_dump/mysqldump via laravel-backup.
                static::run([]);

                return;
            }

            // The sqlite3 CLI is often unavailable, so take a consistent snapshot from PHP
            // and back it up as a file instead of letting laravel-backup dump the database.
            DB::connection($connection)->statement('VACUUM INTO ?', [$snapshotDir.DIRECTORY_SEPARATOR.'database.sqlite']);

            static::run(['--only-files' => true]);
        } finally {
            File::cleanDirectory($snapshotDir);
        }
    }

    public static function delete(string $name, string $disk = 'local'): void
    {
        Storage::disk(static::validatedDisk($disk))->delete(static::pathFor($name, $disk));
    }

    /**
     * Resolve a backup file name to its storage path, refusing anything outside the backup folder.
     */
    public static function pathFor(string $name, string $disk = 'local'): string
    {
        if ($name !== basename($name) || ! str_ends_with($name, '.zip')) {
            throw new RuntimeException('Invalid backup name.');
        }

        $path = static::directory().'/'.$name;

        if (! Storage::disk(static::validatedDisk($disk))->exists($path)) {
            throw new RuntimeException('Backup not found.');
        }

        return $path;
    }

    /**
     * @param  array<string, bool>  $options
     */
    private static function run(array $options): void
    {
        $previousDisks = config('backup.backup.destination.disks');
        config(['backup.backup.destination.disks' => static::disks()]);
        app()->forgetScopedInstances();

        try {
            $exitCode = Artisan::call('backup:run', ['--disable-notifications' => true, ...$options]);

            if ($exitCode !== 0) {
                throw new RuntimeException(trim(Artisan::output()) ?: 'The backup could not be created.');
            }
        } finally {
            config(['backup.backup.destination.disks' => $previousDisks]);
            app()->forgetScopedInstances();
        }
    }

    private static function validatedDisk(string $disk): string
    {
        if (! in_array($disk, static::disks(), true)) {
            throw new RuntimeException('Invalid backup disk.');
        }

        return $disk;
    }
}
