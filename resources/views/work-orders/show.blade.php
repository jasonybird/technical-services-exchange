@php
    $progress = $workOrder->checklistProgress();
    $completed = $workOrder->checklistCompleted();
    $evidenceRules = is_array($workOrder->evidence_rules) ? $workOrder->evidence_rules : [];
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Work Order: {{ $workOrder->jobPost->title }}"
            description="Operational record for the accepted quote, status trail, checklist, evidence, messages, reviews, and peer review."
        >
            <a href="{{ route('work-orders.print', $workOrder) }}" target="_blank" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Print packet</a>
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <section class="tse-panel p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <x-badge tone="sky">{{ str_replace('_', ' ', $workOrder->status) }}</x-badge>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                        Buyer: {{ $workOrder->buyer->buyerProfile?->company_name ?? $workOrder->buyer->name }}
                        | Provider: {{ $workOrder->provider->providerProfile?->business_name ?? $workOrder->provider->name }}
                    </p>
                </div>
                <div class="text-sm text-slate-600 dark:text-slate-400 sm:text-right">
                    <p>Scheduled: {{ $workOrder->scheduled_at?->format('M j, Y g:i A') ?? 'Not set' }}</p>
                    <p>Window: {{ $workOrder->appointment_window ?: 'Not set' }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <x-stat-card label="Checklist" :value="$progress['done'].'/'.$progress['total']" description="Completed deliverables" />
                <x-stat-card label="Attachments" :value="$workOrder->attachments->count()" description="Evidence and files" />
                <x-stat-card label="Changes" :value="count($workOrder->changeRequests())" description="Requested scope changes" />
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
                <div>
                    <h3 class="font-semibold text-slate-950 dark:text-white">Agreed terms</h3>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $workOrder->agreed_terms ?: 'No agreed terms recorded.' }}</p>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-950 dark:text-white">Required evidence</h3>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $workOrder->required_evidence ?: 'No specific evidence requirements recorded.' }}</p>
                    @if ($evidenceRules)
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($evidenceRules as $rule)
                                <x-badge tone="amber">{{ $rule }}</x-badge>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>

        @if (auth()->id() === $workOrder->buyer_id || auth()->user()->hasRole('admin'))
            <section class="tse-panel p-6">
                <h3 class="font-semibold text-slate-950 dark:text-white">Work-order setup</h3>
                <form method="POST" action="{{ route('work-orders.details', $workOrder) }}" class="mt-4 grid gap-4">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-field name="scheduled_at" label="Scheduled date/time" type="datetime-local" :value="$workOrder->scheduled_at?->format('Y-m-d\\TH:i')" />
                        <x-field name="appointment_window" label="Appointment window" :value="$workOrder->appointment_window" />
                    </div>
                    <x-field name="agreed_terms" label="Agreed terms" :value="$workOrder->agreed_terms" textarea />
                    <x-field name="deliverables_checklist" label="Checklist items, one per line" :value="$workOrder->deliverables_checklist" textarea />
                    <x-field name="required_evidence" label="Required evidence" :value="$workOrder->required_evidence" textarea />
                    <div class="grid gap-3 md:grid-cols-3">
                        @foreach (['Arrival photo', 'Completion photo', 'Signed closeout'] as $rule)
                            <label class="inline-flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 dark:border-slate-800 dark:text-slate-300">
                                <input type="checkbox" name="evidence_rules[]" value="{{ $rule }}" @checked(in_array($rule, $evidenceRules, true)) class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950">
                                {{ $rule }}
                            </label>
                        @endforeach
                    </div>
                    <x-primary-button>Save setup</x-primary-button>
                </form>
            </section>
        @endif

        <section class="tse-panel p-6">
            <h3 class="font-semibold text-slate-950 dark:text-white">Checklist and status</h3>
            <form method="POST" action="{{ route('work-orders.transition', $workOrder) }}" class="mt-4 space-y-4">
                @csrf
                @method('PATCH')
                <div class="grid gap-4 md:grid-cols-[.6fr_1.4fr]">
                    <div>
                        <label class="block text-sm font-medium text-slate-800 dark:text-slate-200">Update status</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-slate-300 bg-white text-slate-950 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                            @foreach (\App\Models\WorkOrder::ALLOWED_TRANSITIONS[$workOrder->status] ?? [] as $status)
                                <option value="{{ $status }}">{{ str_replace('_', ' ', $status) }}</option>
                            @endforeach
                            @if (empty(\App\Models\WorkOrder::ALLOWED_TRANSITIONS[$workOrder->status] ?? []))
                                <option value="{{ $workOrder->status }}">{{ str_replace('_', ' ', $workOrder->status) }}</option>
                            @endif
                        </select>
                    </div>
                    <x-field name="completion_notes" label="Completion notes" :value="$workOrder->completion_notes" textarea />
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    @forelse ($workOrder->checklistItems() as $item)
                        <label class="flex items-start gap-3 rounded-md border border-slate-200 p-3 text-sm dark:border-slate-800">
                            <input type="checkbox" name="checklist_completed[{{ $item }}]" value="1" @checked((bool) ($completed[$item] ?? false)) class="mt-1 rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950">
                            <span class="text-slate-700 dark:text-slate-300">{{ $item }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-slate-600 dark:text-slate-400">No checklist items have been defined yet.</p>
                    @endforelse
                </div>

                <x-primary-button>Save status</x-primary-button>
            </form>
        </section>

        <section class="tse-panel p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="font-semibold text-slate-950 dark:text-white">Evidence and files</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Attach photos, closeout forms, notes, screenshots, or other work evidence.</p>
            </div>
            <x-attachments :attachments="$workOrder->attachments" />
            <x-attachment-form type="work_order" :id="$workOrder->id" kind="work_order" />
        </section>

        <section class="tse-panel p-6">
            <h3 class="font-semibold text-slate-950 dark:text-white">Change requests</h3>
            <form method="POST" action="{{ route('work-orders.change-requests', $workOrder) }}" class="mt-4 space-y-4">
                @csrf
                <x-field name="summary" label="Summary" />
                <x-field name="details" label="Details" textarea />
                <x-secondary-button type="submit">Record change request</x-secondary-button>
            </form>
            <div class="mt-4 space-y-3">
                @forelse ($workOrder->changeRequests() as $requestRecord)
                    <div class="rounded-md border border-slate-200 p-4 text-sm dark:border-slate-800">
                        <p class="font-semibold text-slate-950 dark:text-white">{{ $requestRecord['summary'] }}</p>
                        <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $requestRecord['requested_by_name'] ?? 'User' }} | {{ $requestRecord['status'] ?? 'open' }} | {{ $requestRecord['requested_at'] ?? '' }}</p>
                        @if (! empty($requestRecord['details']))
                            <p class="mt-2 whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $requestRecord['details'] }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-600 dark:text-slate-400">No change requests yet.</p>
                @endforelse
            </div>
        </section>

        <section class="tse-panel p-6">
            <h3 class="font-semibold text-slate-950 dark:text-white">Messages</h3>
            <form method="POST" action="{{ route('work-order-messages.store', $workOrder) }}" class="mt-4 space-y-4">
                @csrf
                <x-field name="body" label="Message" textarea />
                <x-primary-button>Send message</x-primary-button>
            </form>
            @foreach ($workOrder->messages as $message)
                <div class="mt-3 rounded-md bg-slate-50 p-3 text-sm dark:bg-slate-950">
                    <p class="font-semibold text-slate-950 dark:text-white">{{ $message->user->name }} | {{ $message->created_at->diffForHumans() }}</p>
                    <p class="whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $message->body }}</p>
                </div>
            @endforeach
        </section>

        <section class="tse-panel p-6">
            <h3 class="font-semibold text-slate-950 dark:text-white">Reviews</h3>
            <form method="POST" action="{{ route('reviews.store', $workOrder) }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-3">
                    <x-field name="rating" label="Rating 1-5" type="number" />
                    <x-field name="communication_rating" label="Communication 1-5" type="number" />
                    <x-field name="scope_accuracy_rating" label="Scope accuracy 1-5" type="number" />
                    <x-field name="payment_reliability_rating" label="Payment reliability 1-5" type="number" />
                    <x-field name="workmanship_rating" label="Workmanship 1-5" type="number" />
                    <x-field name="timeliness_rating" label="Timeliness 1-5" type="number" />
                </div>
                <x-field name="body" label="Review" textarea />
                <x-primary-button>Save review</x-primary-button>
            </form>
            @foreach ($workOrder->reviews as $review)
                <div class="mt-4 rounded-md border border-slate-200 p-4 text-sm dark:border-slate-800">
                    <p class="font-semibold text-slate-950 dark:text-white">{{ $review->reviewer->name }} rated {{ $review->reviewee->name }} {{ $review->rating }}/5</p>
                    <p class="mt-1 text-slate-600 dark:text-slate-400">Communication {{ $review->communication_rating ?? 'n/a' }} | Scope {{ $review->scope_accuracy_rating ?? 'n/a' }} | Payment {{ $review->payment_reliability_rating ?? 'n/a' }} | Workmanship {{ $review->workmanship_rating ?? 'n/a' }} | Timeliness {{ $review->timeliness_rating ?? 'n/a' }}</p>
                    <p class="mt-2 whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $review->body }}</p>
                </div>
            @endforeach
        </section>

        <section class="tse-panel p-6">
            <h3 class="font-semibold text-slate-950 dark:text-white">Community rating</h3>
            <x-rating-summary :ratings="$workOrder->ratings" />
            <x-rating-form type="work_order" :id="$workOrder->id" category="work_order_outcome" mode="thumbs" />
        </section>

        <section class="tse-panel p-6">
            <h3 class="font-semibold text-slate-950 dark:text-white">Disputes / Peer Review</h3>
            <form method="POST" action="{{ route('disputes.store', $workOrder) }}" class="mt-4 space-y-4">
                @csrf
                <x-field name="summary" label="Summary" />
                <x-field name="claim" label="Claim" textarea />
                <x-field name="evidence_notes" label="Evidence notes" textarea />
                <x-primary-button>Open dispute</x-primary-button>
            </form>
            @foreach ($workOrder->disputes as $dispute)
                <a href="{{ route('disputes.show', $dispute) }}" class="mt-4 block rounded-md border border-slate-200 p-4 text-sm dark:border-slate-800">
                    <span class="font-semibold text-slate-950 dark:text-white">{{ $dispute->summary }}</span> | {{ $dispute->status }}
                </a>
            @endforeach
        </section>
    </div>
</x-app-layout>
