<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\JobPost;
use App\Models\WorkOrder;
use App\Models\WorkOrderChangeRequest;
use App\Models\WorkOrderContactEvent;
use App\Notifications\ExchangeEventNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WorkOrderActionController extends Controller
{
    public function availableJobs(Request $request): JsonResponse
    {
        $this->requireAbility($request, 'jobs:read');

        $jobs = JobPost::with('buyer:id,name')
            ->where('visibility', 'public')
            ->whereIn('status', ['open', 'assigned'])
            ->latest()
            ->paginate(min(max((int) $request->integer('per_page', 20), 1), 50));

        return response()->json($jobs);
    }

    public function index(Request $request): JsonResponse
    {
        $this->requireAbility($request, 'work-orders:read');

        $orders = WorkOrder::with('jobPost:id,title,location,scope_clarity_status', 'buyer:id,name', 'provider:id,name')
            ->where(fn ($query) => $query
                ->where('buyer_id', $request->user()->id)
                ->orWhere('provider_id', $request->user()->id)
            )
            ->latest()
            ->paginate(min(max((int) $request->integer('per_page', 20), 1), 50));

        return response()->json($orders);
    }

    public function show(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $this->requireAbility($request, 'work-orders:read');
        $this->authorizeParticipant($request, $workOrder);

        return response()->json([
            'data' => $this->payload($workOrder),
        ]);
    }

    public function transition(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $this->requireAbility($request, 'work-orders:write');
        $this->authorizeParticipant($request, $workOrder);

        $data = $this->validateMobileAction($request, [
            'status' => ['required', 'string', 'in:'.implode(',', WorkOrder::STATUSES)],
            'completion_notes' => ['nullable', 'string', 'max:4000'],
            'occurred_at' => ['nullable', 'date'],
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
            'source' => 'api',
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
        $this->recordMobileEvent($request, $workOrder, 'status_transition', ['status' => $status]);
        $this->notifyOtherParticipant($request, $workOrder, 'Work order updated', $request->user()->name.' changed '.$workOrder->jobPost->title.' to '.str_replace('_', ' ', $status).'.', 'work_order_status');

        return response()->json(['data' => $this->payload($workOrder->fresh())]);
    }

    public function checklist(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $this->requireAbility($request, 'work-orders:write');
        $this->authorizeParticipant($request, $workOrder);

        $data = $this->validateMobileAction($request, [
            'checklist_completed' => ['required', 'array'],
            'checklist_completed.*' => ['nullable', 'boolean'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $completed = collect($workOrder->checklistItems())
            ->mapWithKeys(fn (string $item): array => [$item => (bool) Arr::get($data['checklist_completed'], $item, false)])
            ->all();

        $workOrder->update(['checklist_completed' => $completed]);
        $this->recordMobileEvent($request, $workOrder, 'checklist_update', ['checklist_completed' => $completed]);

        return response()->json(['data' => $this->payload($workOrder->fresh())]);
    }

    public function message(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $this->requireAbility($request, 'work-orders:write');
        $this->authorizeParticipant($request, $workOrder);

        $data = $this->validateMobileAction($request, [
            'body' => ['required', 'string', 'max:4000'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $message = $workOrder->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $this->recordMobileEvent($request, $workOrder, 'message_sent', ['message_id' => $message->id]);
        $this->notifyOtherParticipant($request, $workOrder, 'Work order message', $request->user()->name.' sent a message on '.$workOrder->jobPost->title.'.', 'work_order_message');

        return response()->json(['data' => $message->load('user:id,name')], 201);
    }

    public function evidence(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $this->requireAbility($request, 'work-orders:upload');
        $this->authorizeParticipant($request, $workOrder);

        $maxKb = (int) config('provider-exchange.attachments.max_kb');
        $mimeTypes = (array) config('provider-exchange.attachments.allowed_mime_types');

        $data = $this->validateMobileAction($request, [
            'kind' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'max:'.$maxKb, 'mimetypes:'.implode(',', $mimeTypes)],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $file = $request->file('file');
        $disk = (string) config('provider-exchange.attachments.disk');
        $root = trim((string) config('provider-exchange.attachments.root'), '/');
        $directory = implode('/', array_filter([
            $root ?: 'attachments',
            'work-order',
            now()->format('Y'),
            now()->format('m'),
        ]));

        $attachment = $workOrder->attachments()->create([
            'user_id' => $request->user()->id,
            'kind' => $data['kind'] ?? 'mobile_evidence',
            'disk' => $disk,
            'path' => $file->store($directory, $disk),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
            'caption' => $data['caption'] ?? null,
        ]);

        $this->recordMobileEvent($request, $workOrder, 'evidence_uploaded', ['attachment_id' => $attachment->id]);

        return response()->json(['data' => $attachment], 201);
    }

    public function contactEvent(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $this->requireAbility($request, 'work-orders:write');
        $this->authorizeParticipant($request, $workOrder);
        abort_unless($request->user()->id === $workOrder->provider_id || $request->user()->hasRole('admin'), 403);

        $data = $this->validateMobileAction($request, [
            'event_type' => ['required', 'string', 'in:'.implode(',', array_keys(WorkOrderContactEvent::EVENT_TYPES))],
            'attempted_channel' => ['nullable', 'string', 'max:255'],
            'attempted_at' => ['nullable', 'date'],
            'result' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:4000'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $event = $workOrder->contactEvents()->create([
            'user_id' => $request->user()->id,
            'event_type' => $data['event_type'],
            'attempted_channel' => $data['attempted_channel'] ?? null,
            'attempted_at' => $data['attempted_at'] ?? now(),
            'result' => $data['result'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->recordMobileEvent($request, $workOrder, 'contact_event', ['contact_event_id' => $event->id, 'event_type' => $event->event_type]);
        $workOrder->buyer->notify(new ExchangeEventNotification(
            WorkOrderContactEvent::EVENT_TYPES[$event->event_type] ?? 'Contact event logged',
            $request->user()->name.' logged '.str_replace('_', ' ', $event->event_type).' on '.$workOrder->jobPost->title.'.',
            route('work-orders.show', $workOrder),
            'contact_event'
        ));

        return response()->json(['data' => $event->load('user:id,name')], 201);
    }

    public function runningLate(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $this->requireAbility($request, 'work-orders:write');
        $this->authorizeParticipant($request, $workOrder);
        abort_unless($request->user()->id === $workOrder->provider_id || $request->user()->hasRole('admin'), 403);

        $data = $this->validateMobileAction($request, [
            'estimated_arrival_at' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $event = $this->recordMobileEvent($request, $workOrder, 'running_late', [
            'estimated_arrival_at' => $data['estimated_arrival_at'],
            'reason' => $data['reason'] ?? null,
        ]);

        $workOrder->buyer->notify(new ExchangeEventNotification(
            'Provider running late',
            $request->user()->name.' reported a new estimated arrival for '.$workOrder->jobPost->title.'.',
            route('work-orders.show', $workOrder),
            'running_late'
        ));

        return response()->json(['data' => $event], 201);
    }

    public function scheduleUpdate(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $this->requireAbility($request, 'work-orders:write');
        $this->authorizeParticipant($request, $workOrder);

        $data = $this->validateMobileAction($request, [
            'requested_schedule_at' => ['required', 'date'],
            'summary' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:4000'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $change = $workOrder->structuredChangeRequests()->create([
            'requester_id' => $request->user()->id,
            'reason_code' => 'schedule_change',
            'summary' => $data['summary'],
            'details' => $data['details'] ?? null,
            'schedule_impact' => 'Requested schedule: '.$data['requested_schedule_at'],
            'status' => 'open',
        ]);

        $this->recordMobileEvent($request, $workOrder, 'schedule_update_requested', ['change_request_id' => $change->id]);
        $this->notifyOtherParticipant($request, $workOrder, 'Schedule update requested', $request->user()->name.' requested a schedule update for '.$workOrder->jobPost->title.'.', 'schedule_update_requested');

        return response()->json(['data' => $change->load('requester:id,name')], 201);
    }

    public function dispute(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $this->requireAbility($request, 'disputes:write');
        $this->authorizeParticipant($request, $workOrder);

        $data = $this->validateMobileAction($request, [
            'summary' => ['required', 'string', 'max:255'],
            'reason_code' => ['nullable', 'string', 'in:'.implode(',', array_keys(Dispute::REASON_CODES))],
            'claim' => ['required', 'string', 'max:4000'],
            'evidence_notes' => ['nullable', 'string', 'max:4000'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $dispute = $workOrder->disputes()->create([
            'opened_by_id' => $request->user()->id,
            'status' => 'open',
            'summary' => $data['summary'],
            'reason_code' => $data['reason_code'] ?? 'other',
            'claim' => $data['claim'],
            'evidence_notes' => $data['evidence_notes'] ?? null,
        ]);

        if ($workOrder->status !== 'disputed') {
            $workOrder->update(['status' => 'disputed']);
        }

        $this->recordMobileEvent($request, $workOrder, 'dispute_opened', ['dispute_id' => $dispute->id]);
        $this->notifyOtherParticipant($request, $workOrder, 'Dispute opened', $request->user()->name.' opened a dispute for '.$workOrder->jobPost->title.'.', 'dispute_opened');

        return response()->json(['data' => $dispute->load('openedBy:id,name')], 201);
    }

    private function validateMobileAction(Request $request, array $rules): array
    {
        return $request->validate($rules + [
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);
    }

    private function payload(WorkOrder $workOrder): array
    {
        $workOrder->load(
            'jobPost:id,title,location,scope,scope_clarity_status,risk_flags',
            'buyer:id,name',
            'provider:id,name',
            'messages.user:id,name',
            'attachments',
            'structuredChangeRequests.requester:id,name',
            'contactEvents.user:id,name',
            'disputes.openedBy:id,name',
            'mobileEvents.user:id,name',
        );

        return [
            'id' => $workOrder->id,
            'status' => $workOrder->status,
            'allowed_transitions' => WorkOrder::ALLOWED_TRANSITIONS[$workOrder->status] ?? [],
            'job' => $workOrder->jobPost,
            'buyer' => $workOrder->buyer,
            'provider' => $workOrder->provider,
            'scheduled_at' => $workOrder->scheduled_at,
            'appointment_window' => $workOrder->appointment_window,
            'technician_level' => $workOrder->technicianLevel(),
            'scope' => $workOrder->scope_snapshot,
            'contacts' => $workOrder->contact_snapshot,
            'checklist' => collect($workOrder->checklistItems())->map(fn (string $item): array => [
                'label' => $item,
                'completed' => (bool) ($workOrder->checklistCompleted()[$item] ?? false),
            ])->values(),
            'messages' => $workOrder->messages,
            'attachments' => $workOrder->attachments,
            'change_requests' => $workOrder->structuredChangeRequests,
            'contact_events' => $workOrder->contactEvents,
            'disputes' => $workOrder->disputes,
            'mobile_events' => $workOrder->mobileEvents,
            'privacy' => [
                'geolocation' => 'Optional coordinates are stored only as work-order evidence for the submitted action.',
            ],
        ];
    }

    private function recordMobileEvent(Request $request, WorkOrder $workOrder, string $type, array $payload = [])
    {
        return $workOrder->mobileEvents()->create([
            'user_id' => $request->user()->id,
            'event_type' => $type,
            'payload' => $payload,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'accuracy_meters' => $request->input('accuracy_meters'),
            'occurred_at' => $request->date('occurred_at') ?? now(),
        ]);
    }

    private function requireAbility(Request $request, string $ability): void
    {
        $token = $request->user()->currentAccessToken();

        if ($token && ! $request->user()->tokenCan($ability) && ! $request->user()->tokenCan('*')) {
            abort(403, 'This API token is missing the required ability.');
        }
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

    private function notifyOtherParticipant(Request $request, WorkOrder $workOrder, string $title, string $body, string $type): void
    {
        $recipient = $request->user()->id === $workOrder->buyer_id
            ? $workOrder->provider
            : $workOrder->buyer;

        $recipient->notify(new ExchangeEventNotification($title, $body, route('work-orders.show', $workOrder), $type));
    }
}
