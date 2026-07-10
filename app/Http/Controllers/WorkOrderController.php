<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Notifications\ExchangeEventNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = WorkOrder::with('jobPost', 'buyer.buyerProfile', 'provider.providerProfile')
            ->where(fn ($query) => $query
                ->where('buyer_id', $request->user()->id)
                ->orWhere('provider_id', $request->user()->id)
            )
            ->latest()
            ->paginate(20);

        return view('work-orders.index', ['orders' => $orders]);
    }

    public function show(Request $request, WorkOrder $workOrder): View
    {
        $this->authorizeParticipant($request, $workOrder);

        return view('work-orders.show', [
            'workOrder' => $workOrder->load(
                'jobPost',
                'buyer.buyerProfile',
                'provider.providerProfile',
                'acceptedQuote',
                'reviews.reviewer',
                'reviews.reviewee',
                'disputes.openedBy',
                'messages.user',
                'attachments',
            ),
        ]);
    }

    public function transition(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->authorizeParticipant($request, $workOrder);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', WorkOrder::STATUSES)],
            'completion_notes' => ['nullable', 'string'],
        ]);

        $status = $data['status'];
        abort_unless($workOrder->canTransitionTo($status), 422, 'This status transition is not allowed.');
        $this->authorizeTransitionRole($request, $workOrder, $status);

        $timestamps = [
            'en_route' => 'en_route_at',
            'on_site' => 'on_site_at',
            'in_progress' => 'started_at',
            'completed' => 'completed_at',
            'buyer_approved' => 'approved_at',
            'closed' => 'closed_at',
        ];

        $history = $workOrder->status_history ?? [];
        $history[] = [
            'status' => $status,
            'user_id' => $request->user()->id,
            'at' => now()->toIso8601String(),
        ];

        $updates = [
            'status' => $status,
            'status_history' => $history,
            'completion_notes' => $data['completion_notes'] ?? $workOrder->completion_notes,
        ];

        if (isset($timestamps[$status]) && ! $workOrder->{$timestamps[$status]}) {
            $updates[$timestamps[$status]] = now();
        }

        $workOrder->update($updates);

        $recipient = $request->user()->id === $workOrder->buyer_id
            ? $workOrder->provider
            : $workOrder->buyer;

        $recipient->notify(new ExchangeEventNotification(
            'Work order updated',
            $request->user()->name.' changed '.$workOrder->jobPost->title.' to '.str_replace('_', ' ', $status).'.',
            route('work-orders.show', $workOrder),
            'work_order_status'
        ));

        return redirect()->route('work-orders.show', $workOrder)->with('status', 'Work order updated.');
    }

    private function authorizeParticipant(Request $request, WorkOrder $workOrder): void
    {
        abort_unless(
            in_array($request->user()->id, [$workOrder->buyer_id, $workOrder->provider_id], true)
                || $request->user()->hasRole('admin'),
            403
        );
    }

    private function authorizeTransitionRole(Request $request, WorkOrder $workOrder, string $status): void
    {
        $userId = $request->user()->id;

        $providerStatuses = ['en_route', 'on_site', 'in_progress', 'completed'];
        $buyerStatuses = ['buyer_approved', 'closed', 'cancelled'];
        $sharedStatuses = ['disputed'];

        if (in_array($status, $providerStatuses, true)) {
            abort_unless($workOrder->provider_id === $userId || $request->user()->hasRole('admin'), 403);
        } elseif (in_array($status, $buyerStatuses, true)) {
            abort_unless($workOrder->buyer_id === $userId || $request->user()->hasRole('admin'), 403);
        } elseif (in_array($status, $sharedStatuses, true)) {
            abort_unless(in_array($userId, [$workOrder->buyer_id, $workOrder->provider_id], true) || $request->user()->hasRole('admin'), 403);
        }
    }
}
