<?php

namespace App\Http\Controllers;

use App\Models\Review;
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
            'preparedness_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'scope_accuracy_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'payment_reliability_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'contact_availability_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'schedule_reasonableness_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'support_responsiveness_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'closeout_fairness_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'workmanship_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'timeliness_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'closeout_quality_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'professionalism_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string'],
        ]);

        $review = $workOrder->reviews()
            ->where('reviewer_id', $request->user()->id)
            ->where('reviewee_id', $revieweeId)
            ->first();

        abort_if($review && ! $review->editableBy($request->user()), 422, 'The review edit window has closed.');

        $review = $workOrder->reviews()->updateOrCreate(
            [
                'reviewer_id' => $request->user()->id,
                'reviewee_id' => $revieweeId,
            ],
            $data + [
                'review_type' => $request->user()->id === $workOrder->buyer_id ? 'buyer_to_provider' : 'provider_to_buyer',
                'moderation_status' => $review?->moderation_status ?? 'published',
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

    public function respond(Request $request, Review $review): RedirectResponse
    {
        abort_unless($request->user()->id === $review->reviewee_id || $request->user()->hasRole('admin'), 403);

        $data = $request->validate([
            'response_body' => ['required', 'string', 'max:4000'],
        ]);

        $review->update([
            'response_body' => $data['response_body'],
            'response_at' => now(),
        ]);

        return redirect()->route('work-orders.show', $review->work_order_id)->with('status', 'Review response saved.');
    }

    public function report(Request $request, Review $review): RedirectResponse
    {
        abort_unless(
            in_array($request->user()->id, [$review->reviewer_id, $review->reviewee_id], true)
                || $request->user()->hasRole('admin'),
            403
        );

        $data = $request->validate([
            'report_reason' => ['required', 'string', 'max:4000'],
        ]);

        $review->update([
            'reported_at' => now(),
            'reported_by_id' => $request->user()->id,
            'report_reason' => $data['report_reason'],
            'moderation_status' => 'reported',
        ]);

        return redirect()->route('work-orders.show', $review->work_order_id)->with('status', 'Review reported for moderation.');
    }

    public function moderate(Request $request, Review $review): RedirectResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $data = $request->validate([
            'moderation_status' => ['required', 'string', 'in:'.implode(',', Review::MODERATION_STATUSES)],
            'moderation_notes' => ['nullable', 'string', 'max:4000'],
        ]);

        $review->update([
            'moderation_status' => $data['moderation_status'],
            'moderation_notes' => $data['moderation_notes'] ?? null,
            'moderated_by_id' => $request->user()->id,
            'moderated_at' => now(),
        ]);

        return redirect()->route('admin.index')->with('status', 'Review moderation updated.');
    }
}
