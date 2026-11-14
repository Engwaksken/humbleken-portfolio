<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\SubmissionController;
use App\Http\Controllers\Api\V1\SupportTicketController;
use App\Http\Controllers\ChatbotController;
use App\Support\PageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'service' => config('app.name'),
        ]);
    })->name('health');

    // Public, read-only content.
    Route::middleware('throttle:api-content')->group(function (): void {
        Route::get('/site', [ContentController::class, 'site'])->name('site');
        Route::get('/pages/{key}', [ContentController::class, 'page'])->whereIn('key', PageContent::PAGES)->name('pages.show');
        Route::get('/services', [ContentController::class, 'services'])->name('services.index');
        Route::get('/services/{slug}', [ContentController::class, 'service'])->name('services.show');
        Route::get('/projects', [ContentController::class, 'projects'])->name('projects.index');
        Route::get('/projects/{slug}', [ContentController::class, 'project'])->name('projects.show');
        Route::get('/articles', [ContentController::class, 'articles'])->name('articles.index');
        Route::get('/articles/{slug}', [ContentController::class, 'article'])->name('articles.show');
        Route::get('/article-categories', [ContentController::class, 'articleCategories'])->name('article-categories.index');
        Route::get('/privacy-policy', [ContentController::class, 'privacyPolicy'])->name('privacy-policy');
    });

    // Public submissions.
    Route::post('/enquiries', [SubmissionController::class, 'enquiry'])->middleware('throttle:enquiries')->name('enquiries.store');
    Route::post('/booking-requests', [SubmissionController::class, 'booking'])->middleware('throttle:enquiries')->name('booking-requests.store');
    Route::post('/chatbot/messages', [ChatbotController::class, 'store'])->middleware('throttle:chatbot')->name('chatbot.messages');

    // Customer authentication.
    Route::middleware('throttle:api-auth')->prefix('auth')->name('auth.')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::prefix('auth')->name('auth.')->group(function (): void {
            Route::get('/user', [AuthController::class, 'user'])->name('user');
            Route::put('/notification-preferences', [AuthController::class, 'updateNotificationPreferences'])->name('notification-preferences.update');
            Route::delete('/account', [AuthController::class, 'deleteAccount'])->middleware('throttle:api-auth')->name('account.destroy');
            Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
                ->middleware('throttle:api-auth')
                ->name('verification.send');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });

        // Phones that receive push notifications (Firebase Cloud Messaging).
        Route::prefix('devices')->name('devices.')->middleware('throttle:api-content')->group(function (): void {
            Route::post('/', [DeviceController::class, 'store'])->name('store');
            Route::delete('/', [DeviceController::class, 'destroy'])->name('destroy');
        });

        // Support tickets are for verified customers only, as on the website.
        Route::middleware('verified')->prefix('tickets')->name('tickets.')->group(function (): void {
            Route::get('/', [SupportTicketController::class, 'index'])->name('index');
            Route::get('/{ticket}', [SupportTicketController::class, 'show'])->whereNumber('ticket')->name('show');
            Route::get('/{ticket}/replies/{reply}/attachments/{index}', [SupportTicketController::class, 'attachment'])
                ->whereNumber(['ticket', 'reply', 'index'])
                ->name('attachments');

            Route::middleware('throttle:tickets')->group(function (): void {
                Route::post('/', [SupportTicketController::class, 'store'])->name('store');
                Route::post('/{ticket}/replies', [SupportTicketController::class, 'reply'])->whereNumber('ticket')->name('reply');
            });
        });
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
