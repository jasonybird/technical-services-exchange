<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\AuditLog;
use App\Models\WorkOrderChangeRequest;
use App\Models\WorkOrderContactEvent;
use App\Notifications\ExchangeEventNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = WorkOrder::with('jobPost', 'buyer.buyerProfile', 'provider.providerProfile', 'ratings.user')
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
                'provider.providerProfile.taxonomyTerms',
                'acceptedQuote',
                'structuredChangeRequests.requester',
                'contactEvents.user',
                'providerTagVerification.buyer',
                'reviews.reviewer',
                'reviews.reviewee',
                'disputes.openedBy',
                'messages.user',
                'attachments',
                'ratings.user',
            ),
        ]);
    }

    public function print(Request $request, WorkOrder $workOrder): View
    {
        $this->authorizeParticipant($request, $workOrder);

        return view('work-orders.print', [
            'workOrder' => $workOrder->load(
                'jobPost',
                'buyer.buyerProfile',
                'provider.providerProfile',
                'acceptedQuote',
                'structuredChangeRequests.requester',
                'contactEvents.user',
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
            'checklist_completed' => ['nullable', 'array'],
            'checklist_completed.*' => ['nullable', 'boolean'],
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
            'checklist_completed' => $this->normalizeChecklistCompletion(
                $workOrder,
                $data['checklist_completed'] ?? $workOrder->checklistCompleted()
            ),
        ];

        if (isset($timestamps[$status]) && ! $workOrder->{$timestamps[$status]}) {
            $updates[$timestamps[$status]] = now();
        }

        $workOrder->update($updates);

        AuditLog::record($request, 'work_order.transitioned', $workOrder, [
            'status' => $status,
        ]);

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

    public function updateDetails(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->authorizeParticipant($request, $workOrder);
        abort_unless($request->user()->id === $workOrder->buyer_id || $request->user()->hasRole('admin'), 403);

        $data = $request->validate([
            'scheduled_at' => ['nullable', 'date'],
            'appointment_window' => ['nullable', 'string', 'max:255'],
            'agreed_terms' => ['nullable', 'string'],
            'deliverables_checklist' => ['nullable', 'string'],
            'required_evidence' => ['nullable', 'string'],
            'evidence_rules' => ['nullable', 'array'],
            'evidence_rules.*' => ['nullable', 'string', 'max:255'],
        ]);

        $checklistItems = collect(preg_split('/\r\n|\r|\n/', (string) ($data['deliverables_checklist'] ?? '')))
            ->map(fn (string $item): string => trim($item, " \t\n\r\0\x0B-[]"))
            ->filter()
            ->values()
            ->all();

        $workOrder->update([
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'appointment_window' => $data['appointment_window'] ?? null,
            'agreed_terms' => $data['agreed_terms'] ?? null,
            'deliverables_checklist' => $data['deliverables_checklist'] ?? null,
            'checklist_items' => $checklistItems,
            'checklist_completed' => $this->normalizeChecklistCompletion($workOrder, $workOrder->checklistCompleted(), $checklistItems),
            'required_evidence' => $data['required_evidence'] ?? null,
            'evidence_rules' => array_values(array_filter($data['evidence_rules'] ?? [])),
        ]);

        return redirect()->route('work-orders.show', $workOrder)->with('status', 'Work order details updated.');
    }

    public function requestChange(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->authorizeParticipant($request, $workOrder);

        $data = $request->validate([
            'summary' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string'],
            'reason_code' => ['required', 'string', 'in:'.implode(',', array_keys(WorkOrderChangeRequest::REASON_CODES))],
            'scope_impact' => ['nullable', 'string'],
            'schedule_impact' => ['nullable', 'string'],
            'terms_impact' => ['nullable', 'string'],
        ]);

        $changeRequest = $workOrder->structuredChangeRequests()->create([
            'summary' => $data['summary'],
            'details' => $data['details'] ?? null,
            'reason_code' => $data['reason_code'],
            'scope_impact' => $data['scope_impact'] ?? null,
            'schedule_impact' => $data['schedule_impact'] ?? null,
            'terms_impact' => $data['terms_impact'] ?? null,
            'requester_id' => $request->user()->id,
            'status' => 'open',
        ]);

        AuditLog::record($request, 'work_order.change_requested', $changeRequest, [
            'work_order_id' => $workOrder->id,
            'reason_code' => $changeRequest->reason_code,
        ]);

        $recipient = $request->user()->id === $workOrder->buyer_id
            ? $workOrder->provider
            : $workOrder->buyer;

        $recipient->notify(new ExchangeEventNotification(
            'Work order change requested',
            $request->user()->name.' requested a change on '.$workOrder->jobPost->title.'.',
            route('work-orders.show', $workOrder),
            'work_order_change_request'
        ));

        return redirect()->route('work-orders.show', $workOrder)->with('status', 'Change request recorded.');
    }

    public function resolveChangeRequest(Request $request, WorkOrder $workOrder, WorkOrderChangeRequest $changeRequest): RedirectResponse
    {
        $this->authorizeParticipant($request, $workOrder);
        abort_unless($changeRequest->work_order_id === $workOrder->id, 404);
        abort_unless($request->user()->id !== $changeRequest->requester_id || $request->user()->hasRole('admin'), 403);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:accepted,declined,withdrawn'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        $changeRequest->update([
            'status' => $data['status'],
            'resolution_notes' => $data['resolution_notes'] ?? null,
            'responder_id' => $request->user()->id,
            'responded_at' => now(),
        ]);

        AuditLog::record($request, 'work_order.change_resolved', $changeRequest, [
            'status' => $data['status'],
        ]);

        return redirect()->route('work-orders.show', $workOrder)->with('status', 'Change request updated.');
    }

    public function logContactEvent(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->authorizeParticipant($request, $workOrder);
        abort_unless($request->user()->id === $workOrder->provider_id || $request->user()->hasRole('admin'), 403);

        $data = $request->validate([
            'event_type' => ['required', 'string', 'in:'.implode(',', array_keys(WorkOrderContactEvent::EVENT_TYPES))],
            'attempted_channel' => ['nullable', 'string', 'max:255'],
            'attempted_at' => ['nullable', 'date'],
            'result' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $event = $workOrder->contactEvents()->create($data + [
            'user_id' => $request->user()->id,
            'attempted_at' => $data['attempted_at'] ?? now(),
        ]);

        AuditLog::record($request, 'work_order.contact_event', $event, [
            'work_order_id' => $workOrder->id,
            'event_type' => $event->event_type,
        ]);

        $workOrder->buyer->notify(new ExchangeEventNotification(
            WorkOrderContactEvent::EVENT_TYPES[$event->event_type] ?? 'Contact event logged',
            $request->user()->name.' logged '.str_replace('_', ' ', $event->event_type).' on '.$workOrder->jobPost->title.'.',
            route('work-orders.show', $workOrder),
            'contact_event'
        ));

        return redirect()->route('work-orders.show', $workOrder)->with('status', 'Contact/support event logged.');
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

    private function normalizeChecklistCompletion(WorkOrder $workOrder, array $completed, ?array $items = null): array
    {
        $items ??= $workOrder->checklistItems();

        return collect($items)
            ->mapWithKeys(fn (string $item): array => [$item => (bool) Arr::get($completed, $item, false)])
            ->all();
    }
}
