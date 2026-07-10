<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Notifications\ExchangeEventNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        abort_unless(in_array($request->user()->id, [$workOrder->buyer_id, $workOrder->provider_id], true), 403);

        $revieweeId = $request->user()->id === $workOrder->buyer_id
            ? $workOrder->provider_id
            : $workOrder->buyer_id;

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'communication_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'scope_accuracy_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'payment_reliability_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'workmanship_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'timeliness_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string'],
        ]);

        $workOrder->reviews()->updateOrCreate(
            [
                'reviewer_id' => $request->user()->id,
                'reviewee_id' => $revieweeId,
            ],
            $data + [
                'review_type' => $request->user()->id === $workOrder->buyer_id ? 'buyer_to_provider' : 'provider_to_buyer',
            ]
        );

        $reviewee = $request->user()->id === $workOrder->buyer_id
            ? $workOrder->provider
            : $workOrder->buyer;

        $reviewee->notify(new ExchangeEventNotification(
            'New review received',
            $request->user()->name.' reviewed a completed work order.',
            route('work-orders.show', $workOrder),
            'review_received'
        ));

        return redirect()->route('work-orders.show', $workOrder)->with('status', 'Review saved.');
    }
}
