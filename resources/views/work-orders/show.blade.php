@php
    $progress = $workOrder->checklistProgress();
    $completed = $workOrder->checklistCompleted();
    $evidenceRules = is_array($workOrder->evidence_rules) ? $workOrder->evidence_rules : [];
    $riskFlags = is_array($workOrder->risk_flags) ? $workOrder->risk_flags : [];
    $changeReasonCodes = \App\Models\WorkOrderChangeRequest::REASON_CODES;
    $contactEventTypes = \App\Models\WorkOrderContactEvent::EVENT_TYPES;
    $disputeReasonCodes = \App\Models\Dispute::REASON_CODES;
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
                <x-stat-card label="Contact issues" :value="$workOrder->contactFailureCount()" description="Logged support failures" />
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

        <section class="tse-panel p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="font-semibold text-slate-950 dark:text-white">Scope and support safeguards</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Structured scope governs the work order. Supplemental instructions cannot create undefined onsite obligations.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-badge tone="{{ $workOrder->scope_clarity_status === 'clear' ? 'emerald' : 'amber' }}">Scope {{ str_replace('_', ' ', $workOrder->scope_clarity_status) }}</x-badge>
                    <x-badge tone="sky">{{ $workOrder->technicianLevel()['name'] }}</x-badge>
                    @if ($workOrder->contactSnapshotValue('contact_certified'))
                        <x-badge tone="emerald">Support certified</x-badge>
                    @else
                        <x-badge tone="rose">Support not certified</x-badge>
                    @endif
                </div>
            </div>
            @if ($riskFlags)
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($riskFlags as $flag)
                        <x-badge tone="amber">{{ str_replace('_', ' ', $flag) }}</x-badge>
                    @endforeach
                </div>
            @endif
            <dl class="mt-6 grid gap-4 text-sm md:grid-cols-2">
                <div><dt class="text-slate-500">Primary objective</dt><dd class="whitespace-pre-line text-slate-800 dark:text-slate-200">{{ $workOrder->scopeSnapshotValue('primary_objective') ?: $workOrder->jobPost->scope }}</dd></div>
                <div><dt class="text-slate-500">Expected duration</dt><dd class="whitespace-pre-line text-slate-800 dark:text-slate-200">{{ $workOrder->scopeSnapshotValue('expected_duration') ?: 'Not specified' }}</dd></div>
                <div><dt class="text-slate-500">Included work</dt><dd class="whitespace-pre-line text-slate-800 dark:text-slate-200">{{ $workOrder->scopeSnapshotValue('included_work') ?: 'Not specified' }}</dd></div>
                <div><dt class="text-slate-500">Excluded work</dt><dd class="whitespace-pre-line text-slate-800 dark:text-slate-200">{{ $workOrder->scopeSnapshotValue('excluded_work') ?: 'Not specified' }}</dd></div>
                <div><dt class="text-slate-500">Maximum onsite expectations</dt><dd class="whitespace-pre-line text-slate-800 dark:text-slate-200">{{ $workOrder->scopeSnapshotValue('maximum_onsite_expectations') ?: 'Not specified' }}</dd></div>
                <div><dt class="text-slate-500">Closeout conditions</dt><dd class="whitespace-pre-line text-slate-800 dark:text-slate-200">{{ $workOrder->scopeSnapshotValue('closeout_conditions') ?: 'Not specified' }}</dd></div>
            </dl>
            <div class="mt-6 rounded-md border border-slate-200 p-4 dark:border-slate-800">
                <h4 class="font-semibold text-slate-950 dark:text-white">Certified contact path</h4>
                <dl class="mt-3 grid gap-4 text-sm md:grid-cols-3">
                    <div><dt class="text-slate-500">Primary</dt><dd>{{ $workOrder->contactSnapshotValue('primary_contact_name') ?: 'Not set' }}<br>{{ $workOrder->contactSnapshotValue('primary_contact_phone') }} {{ $workOrder->contactSnapshotValue('primary_contact_email') }}</dd></div>
                    <div><dt class="text-slate-500">Backup</dt><dd>{{ $workOrder->contactSnapshotValue('backup_contact_name') ?: 'Not set' }}<br>{{ $workOrder->contactSnapshotValue('backup_contact_phone') }} {{ $workOrder->contactSnapshotValue('backup_contact_email') }}</dd></div>
                    <div><dt class="text-slate-500">Dispatch</dt><dd>{{ $workOrder->contactSnapshotValue('dispatch_contact_name') ?: 'Not set' }}<br>{{ $workOrder->contactSnapshotValue('dispatch_contact_phone') }} {{ $workOrder->contactSnapshotValue('dispatch_contact_email') }}</dd></div>
                    <div><dt class="text-slate-500">Technical bridge</dt><dd>{{ $workOrder->contactSnapshotValue('technical_bridge') ?: 'Not set' }}</dd></div>
                    <div><dt class="text-slate-500">Escalation</dt><dd>{{ $workOrder->contactSnapshotValue('escalation_contact') ?: 'Not set' }}</dd></div>
                    <div><dt class="text-slate-500">Support window</dt><dd>{{ $workOrder->contactSnapshotValue('support_availability_window') ?: 'Not set' }}<br>{{ $workOrder->contactSnapshotValue('support_channel') }} {{ $workOrder->contactSnapshotValue('support_expected_response_time') }}</dd></div>
                </dl>
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
                <div>
                    <label for="reason_code" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Reason</label>
                    <select id="reason_code" name="reason_code" class="mt-1 block w-full rounded-md border-slate-300 bg-white text-slate-950 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        @foreach ($changeReasonCodes as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <x-field name="summary" label="Summary" />
                <x-field name="details" label="Details" textarea />
                <div class="grid gap-4 md:grid-cols-3">
                    <x-field name="scope_impact" label="Scope impact" textarea />
                    <x-field name="schedule_impact" label="Schedule impact" textarea />
                    <x-field name="terms_impact" label="Terms impact" textarea />
                </div>
                <x-secondary-button type="submit">Record change request</x-secondary-button>
            </form>
            <div class="mt-4 space-y-3">
                @forelse ($workOrder->changeRequests() as $requestRecord)
                    <div class="rounded-md border border-slate-200 p-4 text-sm dark:border-slate-800">
                        <p class="font-semibold text-slate-950 dark:text-white">{{ $requestRecord['summary'] }}</p>
                        <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $requestRecord['requested_by_name'] ?? 'User' }} | {{ $requestRecord['reason_label'] ?? 'Change' }} | {{ $requestRecord['status'] ?? 'open' }} | {{ $requestRecord['requested_at'] ?? '' }}</p>
                        @if (! empty($requestRecord['details']))
                            <p class="mt-2 whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $requestRecord['details'] }}</p>
                        @endif
                        <div class="mt-3 grid gap-3 text-xs md:grid-cols-3">
                            <p><span class="font-semibold">Scope:</span> {{ $requestRecord['scope_impact'] ?? 'Not specified' }}</p>
                            <p><span class="font-semibold">Schedule:</span> {{ $requestRecord['schedule_impact'] ?? 'Not specified' }}</p>
                            <p><span class="font-semibold">Terms:</span> {{ $requestRecord['terms_impact'] ?? 'Not specified' }}</p>
                        </div>
                        @if (! empty($requestRecord['id']) && ($requestRecord['status'] ?? 'open') === 'open' && (auth()->id() !== ($requestRecord['requested_by_id'] ?? null) || auth()->user()->hasRole('admin')))
                            <form method="POST" action="{{ route('work-orders.change-requests.resolve', [$workOrder, $requestRecord['id']]) }}" class="mt-3 grid gap-3 border-t border-slate-200 pt-3 dark:border-slate-800 md:grid-cols-[.6fr_1.4fr_auto]">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="rounded-md border-slate-300 bg-white text-sm text-slate-950 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                                    <option value="accepted">Accept</option>
                                    <option value="declined">Decline</option>
                                    <option value="withdrawn">Withdraw</option>
                                </select>
                                <input name="resolution_notes" class="rounded-md border-slate-300 bg-white text-sm text-slate-950 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" placeholder="Resolution notes">
                                <x-secondary-button type="submit">Update</x-secondary-button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-600 dark:text-slate-400">No change requests yet.</p>
                @endforelse
            </div>
        </section>

        <section class="tse-panel p-6">
            <h3 class="font-semibold text-slate-950 dark:text-white">Contact and support event log</h3>
            @if (auth()->id() === $workOrder->provider_id || auth()->user()->hasRole('admin'))
                <form method="POST" action="{{ route('work-orders.contact-events.store', $workOrder) }}" class="mt-4 space-y-4">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="event_type" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Event type</label>
                            <select id="event_type" name="event_type" class="mt-1 block w-full rounded-md border-slate-300 bg-white text-slate-950 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                                @foreach ($contactEventTypes as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-field name="attempted_channel" label="Attempted channel" />
                        <x-field name="attempted_at" label="Attempted at" type="datetime-local" />
                        <x-field name="result" label="Result" />
                    </div>
                    <x-field name="notes" label="Notes" textarea />
                    <x-secondary-button type="submit">Log contact/support event</x-secondary-button>
                </form>
            @endif
            <div class="mt-4 space-y-3">
                @forelse ($workOrder->contactEvents as $event)
                    <div class="rounded-md border border-slate-200 p-4 text-sm dark:border-slate-800">
                        <p class="font-semibold text-slate-950 dark:text-white">{{ $contactEventTypes[$event->event_type] ?? str_replace('_', ' ', $event->event_type) }}</p>
                        <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $event->user->name }} | {{ $event->attempted_at?->format('M j, Y g:i A') ?? $event->created_at->format('M j, Y g:i A') }} | {{ $event->attempted_channel ?: 'No channel' }} | {{ $event->result ?: 'No result' }}</p>
                        @if ($event->notes)
                            <p class="mt-2 whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $event->notes }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-600 dark:text-slate-400">No contact/support events have been logged.</p>
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
            @php
                $reviewType = auth()->id() === $workOrder->buyer_id ? 'buyer_to_provider' : 'provider_to_buyer';
                $definitions = config("reputation.definitions.$reviewType", []);
                $reviewFields = $reviewType === 'buyer_to_provider'
                    ? [
                        'communication_rating' => 'Communication',
                        'preparedness_rating' => 'Preparedness',
                        'workmanship_rating' => 'Workmanship',
                        'timeliness_rating' => 'Timeliness',
                        'closeout_quality_rating' => 'Closeout quality',
                        'professionalism_rating' => 'Professionalism',
                    ]
                    : [
                        'communication_rating' => 'Communication',
                        'scope_accuracy_rating' => 'Scope accuracy',
                        'payment_reliability_rating' => 'Payment reliability',
                        'contact_availability_rating' => 'Contact availability',
                        'schedule_reasonableness_rating' => 'Schedule reasonableness',
                        'support_responsiveness_rating' => 'Support responsiveness',
                        'closeout_fairness_rating' => 'Closeout fairness',
                    ];
                $fieldDefinitionKeys = [
                    'communication_rating' => 'communication',
                    'preparedness_rating' => 'preparedness',
                    'workmanship_rating' => 'workmanship',
                    'timeliness_rating' => 'timeliness',
                    'closeout_quality_rating' => 'closeout_quality',
                    'professionalism_rating' => 'professionalism',
                    'scope_accuracy_rating' => 'scope_accuracy',
                    'payment_reliability_rating' => 'payment_reliability',
                    'contact_availability_rating' => 'contact_availability',
                    'schedule_reasonableness_rating' => 'schedule_reasonableness',
                    'support_responsiveness_rating' => 'support_responsiveness',
                    'closeout_fairness_rating' => 'closeout_fairness',
                ];
            @endphp
            <div class="mt-3 rounded-md border border-sky-200 bg-sky-50 p-4 text-sm text-sky-950 dark:border-sky-900 dark:bg-sky-950/40 dark:text-sky-100">
                <p class="font-semibold">Transparent review rules</p>
                <p class="mt-1">{{ $definitions['overall'] ?? 'Reviews stay five-star based and visible. Category scores explain the evidence instead of hiding it behind a platform score.' }}</p>
                <p class="mt-2 text-xs">Reviews can be edited for {{ config('reputation.review_edit_window_hours', 48) }} hours, responded to by the reviewee, and reported for moderation.</p>
            </div>
            <form method="POST" action="{{ route('reviews.store', $workOrder) }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <x-field name="rating" label="Overall rating 1-5" type="number" />
                    </div>
                    @foreach ($reviewFields as $field => $label)
                        @php
                            $definitionKey = $fieldDefinitionKeys[$field] ?? null;
                        @endphp
                        <div>
                            <x-field :name="$field" :label="$label.' 1-5'" type="number" />
                            @if ($definitionKey && ! empty($definitions[$definitionKey]))
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $definitions[$definitionKey] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                <x-field name="body" label="Review" textarea />
                <x-primary-button>Save review</x-primary-button>
            </form>
            @foreach ($workOrder->reviews as $review)
                @php
                    $isHidden = $review->moderation_status === 'hidden';
                    $categoryValues = [
                        'Communication' => $review->communication_rating,
                        'Preparedness' => $review->preparedness_rating,
                        'Scope' => $review->scope_accuracy_rating,
                        'Payment' => $review->payment_reliability_rating,
                        'Contact availability' => $review->contact_availability_rating,
                        'Schedule' => $review->schedule_reasonableness_rating,
                        'Support' => $review->support_responsiveness_rating,
                        'Closeout fairness' => $review->closeout_fairness_rating,
                        'Workmanship' => $review->workmanship_rating,
                        'Timeliness' => $review->timeliness_rating,
                        'Closeout quality' => $review->closeout_quality_rating,
                        'Professionalism' => $review->professionalism_rating,
                    ];
                    $categoryLine = collect($categoryValues)
                        ->filter(fn ($value) => $value !== null)
                        ->map(fn ($value, $label) => "$label $value")
                        ->implode(' | ');
                @endphp
                <div class="mt-4 rounded-md border border-slate-200 p-4 text-sm dark:border-slate-800">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-semibold text-slate-950 dark:text-white">{{ $review->reviewer->name }} rated {{ $review->reviewee->name }} {{ $review->rating }}/5</p>
                            <p class="mt-1 text-slate-600 dark:text-slate-400">{{ str_replace('_', ' ', $review->review_type) }} | {{ $review->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if ($review->moderation_status === 'reported')
                                <x-badge tone="amber">Reported</x-badge>
                            @elseif ($isHidden)
                                <x-badge tone="rose">Hidden</x-badge>
                            @else
                                <x-badge tone="emerald">Published</x-badge>
                            @endif
                            @if ($review->editableBy(auth()->user()))
                                <x-badge tone="slate">Editable</x-badge>
                            @endif
                        </div>
                    </div>
                    @if ($isHidden && ! auth()->user()->hasRole('admin'))
                        <p class="mt-3 text-slate-600 dark:text-slate-400">This review is hidden pending moderation.</p>
                    @else
                        @if ($categoryLine)
                            <p class="mt-2 text-slate-600 dark:text-slate-400">{{ $categoryLine }}</p>
                        @endif
                        <p class="mt-2 whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $review->body }}</p>
                        @if ($review->response_body)
                            <div class="mt-3 rounded-md bg-slate-50 p-3 dark:bg-slate-950">
                                <p class="font-semibold text-slate-950 dark:text-white">Response from {{ $review->reviewee->name }}</p>
                                <p class="mt-1 whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $review->response_body }}</p>
                            </div>
                        @elseif (auth()->id() === $review->reviewee_id || auth()->user()->hasRole('admin'))
                            <form method="POST" action="{{ route('reviews.respond', $review) }}" class="mt-3 space-y-3 rounded-md bg-slate-50 p-3 dark:bg-slate-950">
                                @csrf
                                <x-field name="response_body" label="Public response" textarea />
                                <x-secondary-button type="submit">Save response</x-secondary-button>
                            </form>
                        @endif
                        @if (in_array(auth()->id(), [$review->reviewer_id, $review->reviewee_id], true) || auth()->user()->hasRole('admin'))
                            <form method="POST" action="{{ route('reviews.report', $review) }}" class="mt-3 space-y-3 border-t border-slate-200 pt-3 dark:border-slate-800">
                                @csrf
                                <x-field name="report_reason" label="Report this review for moderation" textarea />
                                <x-secondary-button type="submit">Report review</x-secondary-button>
                            </form>
                        @endif
                    @endif
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
                <div>
                    <label for="dispute_reason_code" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Reason code</label>
                    <select id="dispute_reason_code" name="reason_code" class="mt-1 block w-full rounded-md border-slate-300 bg-white text-slate-950 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        @foreach ($disputeReasonCodes as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
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
