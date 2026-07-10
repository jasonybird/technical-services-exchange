<?php

use App\Http\Controllers\Api\V1\WorkOrderActionController;
use App\Models\Dispute;
use App\Models\JobPost;
use App\Models\ProviderProfile;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/providers', function () {
    return ProviderProfile::with('user:id,name')->latest()->paginate(20);
});

Route::get('/jobs', function () {
    return JobPost::with('buyer:id,name')->latest()->paginate(20);
});

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/me', function (Request $request) {
        return $request->user()->load('roles', 'providerProfile', 'buyerProfile')
            ->setAttribute('unread_notifications_count', $request->user()->unreadNotifications()->count());
    });

    Route::get('/jobs', [WorkOrderActionController::class, 'availableJobs']);
    Route::get('/work-orders', [WorkOrderActionController::class, 'index']);
    Route::get('/work-orders/{workOrder}', [WorkOrderActionController::class, 'show']);
    Route::patch('/work-orders/{workOrder}/transition', [WorkOrderActionController::class, 'transition']);
    Route::patch('/work-orders/{workOrder}/checklist', [WorkOrderActionController::class, 'checklist']);
    Route::post('/work-orders/{workOrder}/messages', [WorkOrderActionController::class, 'message']);
    Route::post('/work-orders/{workOrder}/evidence', [WorkOrderActionController::class, 'evidence']);
    Route::post('/work-orders/{workOrder}/contact-events', [WorkOrderActionController::class, 'contactEvent']);
    Route::post('/work-orders/{workOrder}/running-late', [WorkOrderActionController::class, 'runningLate']);
    Route::post('/work-orders/{workOrder}/schedule-updates', [WorkOrderActionController::class, 'scheduleUpdate']);
    Route::post('/work-orders/{workOrder}/disputes', [WorkOrderActionController::class, 'dispute']);
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/me', function (Request $request) {
        return $request->user()->load('roles', 'providerProfile', 'buyerProfile')
            ->setAttribute('unread_notifications_count', $request->user()->unreadNotifications()->count());
    });

    Route::get('/work-orders', function (Request $request) {
        return WorkOrder::with('jobPost:id,title,location', 'buyer:id,name', 'provider:id,name', 'messages.user:id,name', 'reviews', 'disputes')
            ->where(fn ($query) => $query
                ->where('buyer_id', $request->user()->id)
                ->orWhere('provider_id', $request->user()->id)
            )
            ->latest()
            ->paginate(20);
    });

    Route::get('/disputes', function (Request $request) {
        return Dispute::with('workOrder.jobPost:id,title', 'openedBy:id,name', 'votes.user:id,name')
            ->whereHas('workOrder', fn ($query) => $query
                ->where('buyer_id', $request->user()->id)
                ->orWhere('provider_id', $request->user()->id)
            )
            ->latest()
            ->paginate(20);
    });
});
