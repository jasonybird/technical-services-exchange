<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\BuyerProfileController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\DisputeVoteController;
use App\Http\Controllers\ExternalProfileImportController;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProviderProfileController;
use App\Http\Controllers\ProviderTagVerificationController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SocialPostController;
use App\Http\Controllers\WorkOrderMessageController;
use App\Http\Controllers\WorkOrderController;
use App\Models\BuyerProfile;
use App\Models\Dispute;
use App\Models\JobPost;
use App\Models\ProviderProfile;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Route;

$basePath = trim((string) config('app.base_path', ''), '/');

Route::prefix($basePath)->group(function (): void {
Route::get('/', function () {
    return view('welcome');
});

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/dashboard', function () {
    return view('dashboard', [
        'counts' => [
            'providers' => ProviderProfile::count(),
            'buyers' => BuyerProfile::count(),
            'jobs' => JobPost::count(),
            'workOrders' => WorkOrder::count(),
            'disputes' => Dispute::count(),
        ],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/provider-profile', [ProviderProfileController::class, 'edit'])->name('providers.edit');
    Route::put('/provider-profile', [ProviderProfileController::class, 'update'])->name('providers.update');
    Route::post('/provider-profile/imports', [ExternalProfileImportController::class, 'store'])->name('provider-imports.store');
    Route::patch('/provider-profile/imports/{import}/verify', [ExternalProfileImportController::class, 'verify'])->name('provider-imports.verify');

    Route::get('/buyer-profile', [BuyerProfileController::class, 'edit'])->name('buyers.edit');
    Route::put('/buyer-profile', [BuyerProfileController::class, 'update'])->name('buyers.update');

    Route::post('/feed', [SocialPostController::class, 'store'])->name('feed.store');
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    Route::post('/ratings', [RatingController::class, 'store'])->name('ratings.store');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('/jobs/create', [JobPostController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobPostController::class, 'store'])->name('jobs.store');
    Route::post('/jobs/{job}/quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::patch('/quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::post('/quotes/{quote}/accept', [QuoteController::class, 'accept'])->name('quotes.accept');
    Route::post('/quotes/{quote}/decline', [QuoteController::class, 'decline'])->name('quotes.decline');

    Route::get('/work-orders', [WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::get('/work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('work-orders.show');
    Route::get('/work-orders/{workOrder}/print', [WorkOrderController::class, 'print'])->name('work-orders.print');
    Route::patch('/work-orders/{workOrder}/details', [WorkOrderController::class, 'updateDetails'])->name('work-orders.details');
    Route::post('/work-orders/{workOrder}/change-requests', [WorkOrderController::class, 'requestChange'])->name('work-orders.change-requests');
    Route::patch('/work-orders/{workOrder}/change-requests/{changeRequest}', [WorkOrderController::class, 'resolveChangeRequest'])->name('work-orders.change-requests.resolve');
    Route::post('/work-orders/{workOrder}/contact-events', [WorkOrderController::class, 'logContactEvent'])->name('work-orders.contact-events.store');
    Route::patch('/work-orders/{workOrder}/transition', [WorkOrderController::class, 'transition'])->name('work-orders.transition');
    Route::post('/work-orders/{workOrder}/messages', [WorkOrderMessageController::class, 'store'])->name('work-order-messages.store');
    Route::post('/work-orders/{workOrder}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/work-orders/{workOrder}/provider-tag-verification', [ProviderTagVerificationController::class, 'store'])->name('provider-tag-verifications.store');
    Route::post('/reviews/{review}/response', [ReviewController::class, 'respond'])->name('reviews.respond');
    Route::post('/reviews/{review}/report', [ReviewController::class, 'report'])->name('reviews.report');
    Route::patch('/reviews/{review}/moderation', [ReviewController::class, 'moderate'])->name('reviews.moderate');
    Route::post('/work-orders/{workOrder}/disputes', [DisputeController::class, 'store'])->name('disputes.store');
    Route::get('/disputes/{dispute}', [DisputeController::class, 'show'])->name('disputes.show');
    Route::post('/disputes/{dispute}/votes', [DisputeVoteController::class, 'store'])->name('dispute-votes.store');

    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
});

Route::get('/providers', [ProviderProfileController::class, 'index'])->name('providers.index');
Route::get('/providers/{provider}', [ProviderProfileController::class, 'show'])->name('providers.show');
Route::get('/buyers', [BuyerProfileController::class, 'index'])->name('buyers.index');
Route::get('/buyers/{buyer}', [BuyerProfileController::class, 'show'])->name('buyers.show');
Route::get('/feed', [SocialPostController::class, 'index'])->name('feed.index');
Route::get('/jobs', [JobPostController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobPostController::class, 'show'])->name('jobs.show');

require __DIR__.'/auth.php';
});
