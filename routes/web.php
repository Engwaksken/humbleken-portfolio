<?php

use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\SupportTicketController;
use App\Http\Controllers\Account\TicketAttachmentController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\BookingRequestController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PublicMediaController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SitePageController;
use App\Http\Middleware\EnsureAdminMfaForTicketAttachments;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/services', [SitePageController::class, 'services'])->name('services.index');
Route::get('/services/{slug}', [SitePageController::class, 'service'])->name('services.show');
Route::get('/approach', [SitePageController::class, 'approach'])->name('approach');
Route::get('/booking', [SitePageController::class, 'booking'])->name('booking');
Route::get('/contact', [SitePageController::class, 'contact'])->name('contact');
Route::get('/site-assets/{key}', [PublicMediaController::class, 'branding'])
    ->whereIn('key', ['logo', 'favicon'])
    ->name('site-assets.show');
Route::get('/samples/{type}/{record}', [PublicMediaController::class, 'sample'])
    ->whereIn('type', ['project', 'service'])
    ->whereNumber('record')
    ->name('samples.show');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::post('/enquiries', [EnquiryController::class, 'store'])
    ->middleware('throttle:enquiries')
    ->name('enquiries.store');

Route::post('/booking-requests', [BookingRequestController::class, 'store'])
    ->middleware('throttle:enquiries')
    ->name('booking-requests.store');
Route::post('/chatbot/messages', [ChatbotController::class, 'store'])
    ->middleware('throttle:chatbot')
    ->name('chatbot.messages');

Route::middleware(['auth', 'verified'])->prefix('account')->name('account.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [SupportTicketController::class, 'create'])->name('tickets.create');
    Route::get('/tickets/{ticket}', [SupportTicketController::class, 'show'])->whereNumber('ticket')->name('tickets.show');
    Route::get('/ticket-replies/{reply}/attachments/{attachment}', TicketAttachmentController::class)
        ->whereNumber(['reply', 'attachment'])
        ->middleware(EnsureAdminMfaForTicketAttachments::class)
        ->name('tickets.attachments.download');

    Route::middleware('throttle:tickets')->group(function (): void {
        Route::post('/tickets', [SupportTicketController::class, 'store'])->name('tickets.store');
        Route::post('/tickets/{ticket}/replies', [SupportTicketController::class, 'reply'])->whereNumber('ticket')->name('tickets.reply');
    });
});
