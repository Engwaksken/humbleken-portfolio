<?php

use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\SupportTicketController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::post('/enquiries', [EnquiryController::class, 'store'])
    ->middleware('throttle:enquiries')
    ->name('enquiries.store');

Route::middleware(['auth', 'verified'])->prefix('account')->name('account.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [SupportTicketController::class, 'create'])->name('tickets.create');
    Route::get('/tickets/{ticket}', [SupportTicketController::class, 'show'])->whereNumber('ticket')->name('tickets.show');

    Route::middleware('throttle:tickets')->group(function (): void {
        Route::post('/tickets', [SupportTicketController::class, 'store'])->name('tickets.store');
        Route::post('/tickets/{ticket}/replies', [SupportTicketController::class, 'reply'])->whereNumber('ticket')->name('tickets.reply');
    });
});
