<?php

use App\Http\Middleware\PublicMaintenanceMode;
use App\Http\Middleware\SecurityHeaders;
use App\Support\Backups;
use App\Support\Settings;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SecurityHeaders::class);
        $middleware->web(append: [PublicMaintenanceMode::class]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->call(fn () => Backups::create())
            ->everyMinute()
            ->name('automatic-site-backup')
            ->withoutOverlapping()
            ->when(fn (): bool => filter_var(Settings::get('backup_schedule_enabled', false), FILTER_VALIDATE_BOOLEAN)
                && now()->format('H:i') === Settings::get('backup_schedule_time', '02:00'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
