<?php

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

Route::middleware('auth:sanctum')->group(function () {
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
