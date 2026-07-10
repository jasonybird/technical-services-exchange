<?php

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
        return $request->user()->load('roles', 'providerProfile', 'buyerProfile');
    });

    Route::get('/work-orders', function (Request $request) {
        return WorkOrder::with('jobPost:id,title,location', 'buyer:id,name', 'provider:id,name')
            ->where(fn ($query) => $query
                ->where('buyer_id', $request->user()->id)
                ->orWhere('provider_id', $request->user()->id)
            )
            ->latest()
            ->paginate(20);
    });
});
