<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">{{ $job->title }}</h2></x-slot>
    <div class="mx-auto max-w-6xl space-y-6 p-6">
        <section class="rounded border bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm text-gray-500">{{ $job->location }} | {{ $job->service_category }} | {{ $job->status }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <x-badge tone="{{ $job->scope_clarity_status === 'clear' ? 'emerald' : 'amber' }}">Scope {{ str_replace('_', ' ', $job->scope_clarity_status) }}</x-badge>
                        <x-badge tone="sky">{{ $job->technicianLevel()['name'] }}</x-badge>
                        @if ($job->workCategory)
                            <x-badge tone="slate">{{ $job->workCategory->name }}</x-badge>
                        @endif
                        @if ($job->contact_certified)
                            <x-badge tone="emerald">Support certified</x-badge>
                        @else
                            <x-badge tone="rose">Support not certified</x-badge>
                        @endif
                        @if ($job->remote_eligible)
                            <x-badge tone="sky">Remote eligible</x-badge>
                        @endif
                    </div>
                </div>
                <div class="text-sm text-slate-600 dark:text-slate-400 sm:text-right">
                    <p>{{ $job->starts_at?->format('M j, Y g:i A') ?? 'No start set' }}</p>
                    <p>{{ $job->time_window ?: 'No window set' }} | {{ str_replace('_', ' ', $job->schedule_type ?: 'schedule not set') }}</p>
                    <p>{{ str_replace('_', ' ', $job->work_mode ?: 'onsite') }} | {{ str_replace('_', ' ', $job->pay_type ?: 'pay not listed') }}</p>
                </div>
            </div>

            @if (is_array($job->risk_flags) && $job->risk_flags)
                <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100">
                    <p class="font-semibold">Risk flags</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($job->risk_flags as $flag)
                            <x-badge tone="amber">{{ str_replace('_', ' ', $flag) }}</x-badge>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-6 rounded-md border border-sky-200 bg-sky-50 p-4 text-sm text-sky-950 dark:border-sky-900 dark:bg-sky-950/40 dark:text-sky-100">
                <p class="font-semibold">Anti-catch-all rule</p>
                <p class="mt-1">The structured scope fields define this job. Supplemental instructions are preserved for reference, but they do not override included work, excluded work, closeout conditions, or change-request requirements.</p>
            </div>

            <dl class="mt-6 grid gap-4 md:grid-cols-2">
                <div><dt class="text-sm text-gray-500">Primary objective</dt><dd class="whitespace-pre-line">{{ $job->primary_objective ?: $job->scope }}</dd></div>
                <div><dt class="text-sm text-gray-500">Technician level</dt><dd class="whitespace-pre-line">{{ $job->technicianLevel()['description'] }}</dd></div>
                <div><dt class="text-sm text-gray-500">Level scope rule</dt><dd class="whitespace-pre-line">{{ $job->technicianLevel()['scope_rule'] }}</dd></div>
                <div><dt class="text-sm text-gray-500">Expected duration</dt><dd class="whitespace-pre-line">{{ $job->expected_duration ?: 'Not specified' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Included work</dt><dd class="whitespace-pre-line">{{ $job->included_work ?: 'Not specified' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Excluded work</dt><dd class="whitespace-pre-line">{{ $job->excluded_work ?: 'Not specified' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Maximum onsite expectations</dt><dd class="whitespace-pre-line">{{ $job->maximum_onsite_expectations ?: 'Not specified' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Closeout conditions</dt><dd class="whitespace-pre-line">{{ $job->closeout_conditions ?: 'Not specified' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Required skills</dt><dd class="whitespace-pre-line">{{ $job->required_skills }}</dd></div>
                <div><dt class="text-sm text-gray-500">Required tools</dt><dd class="whitespace-pre-line">{{ $job->required_tools }}</dd></div>
                <div><dt class="text-sm text-gray-500">Required certifications</dt><dd class="whitespace-pre-line">{{ $job->required_certifications ?: 'None listed' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Safety gear</dt><dd class="whitespace-pre-line">{{ $job->required_safety_gear ?: 'None listed' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Deliverables</dt><dd class="whitespace-pre-line">{{ $job->deliverables }}</dd></div>
                <div><dt class="text-sm text-gray-500">Payment terms</dt><dd class="whitespace-pre-line">{{ $job->payment_terms }}</dd></div>
                <div><dt class="text-sm text-gray-500">Buyer-provided equipment</dt><dd class="whitespace-pre-line">{{ $job->buyer_provided_equipment ?: 'Not specified' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Provider-provided equipment</dt><dd class="whitespace-pre-line">{{ $job->provider_provided_equipment ?: 'Not specified' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Return shipment</dt><dd class="whitespace-pre-line">{{ $job->return_shipment_expectations ?: 'Not specified' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Parking/access</dt><dd class="whitespace-pre-line">{{ $job->parking_access_notes ?: 'Not specified' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Onsite restrictions</dt><dd class="whitespace-pre-line">{{ $job->onsite_restrictions ?: 'Not specified' }}</dd></div>
                <div><dt class="text-sm text-gray-500">Vendor onboarding</dt><dd class="whitespace-pre-line">{{ $job->vendor_onboarding ?: 'Not specified' }}</dd></div>
            </dl>
            @if ($job->supplemental_instructions)
                <details class="mt-6 rounded-md border border-slate-200 p-4 text-sm dark:border-slate-800">
                    <summary class="cursor-pointer font-semibold text-slate-950 dark:text-white">Supplemental instructions</summary>
                    <p class="mt-3 whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $job->supplemental_instructions }}</p>
                </details>
            @endif
            <section class="mt-6 rounded-md border border-slate-200 p-4 dark:border-slate-800">
                <h3 class="font-semibold text-slate-950 dark:text-white">Contact and support coverage</h3>
                <dl class="mt-3 grid gap-4 text-sm md:grid-cols-3">
                    <div><dt class="text-gray-500">Primary contact</dt><dd>{{ $job->primary_contact_name ?: 'Not set' }}<br>{{ $job->primary_contact_phone }} {{ $job->primary_contact_email }}</dd></div>
                    <div><dt class="text-gray-500">Backup contact</dt><dd>{{ $job->backup_contact_name ?: 'Not set' }}<br>{{ $job->backup_contact_phone }} {{ $job->backup_contact_email }}</dd></div>
                    <div><dt class="text-gray-500">Dispatch contact</dt><dd>{{ $job->dispatch_contact_name ?: 'Not set' }}<br>{{ $job->dispatch_contact_phone }} {{ $job->dispatch_contact_email }}</dd></div>
                    <div><dt class="text-gray-500">Technical bridge</dt><dd>{{ $job->technical_bridge ?: 'Not set' }}</dd></div>
                    <div><dt class="text-gray-500">Escalation</dt><dd>{{ $job->escalation_contact ?: 'Not set' }}</dd></div>
                    <div><dt class="text-gray-500">Support availability</dt><dd>{{ $job->support_availability_window ?: 'Not set' }}<br>{{ $job->support_channel }} {{ $job->support_expected_response_time }}</dd></div>
                </dl>
            </section>
            <x-attachments :attachments="$job->attachments" />
            <x-rating-summary :ratings="$job->ratings" />
            <x-rating-form type="job_post" :id="$job->id" category="job_quality" />
            @auth
                @if (auth()->id() === $job->buyer_id)
                    <x-attachment-form type="job_post" :id="$job->id" kind="job" />
                @endif
            @endauth
        </section>
        @auth
            @role('provider')
                @if ($job->status === 'open' && $job->buyer_id !== auth()->id())
                    <form method="POST" action="{{ route('quotes.store', $job) }}" class="space-y-4 rounded border bg-white p-6">
                        @csrf
                        <h3 class="font-semibold">Submit quote</h3>
                        <x-field name="requested_amount" label="Requested amount" type="number" />
                        <x-field name="rate_summary" label="Rate summary" />
                        <x-field name="message" label="Message" textarea />
                        <x-field name="terms" label="Terms" textarea />
                        <x-primary-button>Submit quote</x-primary-button>
                    </form>
                @endif
            @endrole
        @endauth
        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Quotes</h3>
            @forelse ($job->quotes as $quote)
                <div class="mt-4 rounded border p-4">
                    <p class="font-semibold">{{ $quote->provider->providerProfile?->business_name ?? $quote->provider->name }}</p>
                    <p class="text-sm text-gray-600">{{ $quote->status }} | {{ $quote->rate_summary }} | {{ $quote->requested_amount }}</p>
                    <p class="mt-2 whitespace-pre-line text-sm">{{ $quote->message }}</p>
                    @if ($quote->revisions->count())
                        <details class="mt-3 text-sm">
                            <summary class="cursor-pointer font-semibold">Revision history</summary>
                            @foreach ($quote->revisions as $revision)
                                <p class="mt-2 text-gray-600">{{ $revision->created_at->diffForHumans() }} | {{ $revision->action }} | {{ $revision->rate_summary }} | {{ $revision->requested_amount }}</p>
                            @endforeach
                        </details>
                    @endif
                    @auth
                        @if (auth()->id() === $quote->provider_id && in_array($quote->status, ['submitted', 'countered', 'revised'], true) && $job->status === 'open')
                            <form method="POST" action="{{ route('quotes.update', $quote) }}" class="mt-4 space-y-3 rounded bg-gray-50 p-3">
                                @csrf @method('PATCH')
                                <x-field name="requested_amount" label="Revised amount" type="number" :value="$quote->requested_amount" />
                                <x-field name="rate_summary" label="Rate summary" :value="$quote->rate_summary" />
                                <x-field name="message" label="Message" :value="$quote->message" textarea />
                                <x-field name="terms" label="Terms" :value="$quote->terms" textarea />
                                <x-primary-button>Revise quote</x-primary-button>
                            </form>
                        @endif
                        @if (auth()->id() === $job->buyer_id && $job->status === 'open')
                            <form method="POST" action="{{ route('quotes.accept', $quote) }}" class="mt-3 inline-block">
                                @csrf
                                <x-primary-button>Accept quote</x-primary-button>
                            </form>
                            <form method="POST" action="{{ route('quotes.decline', $quote) }}" class="mt-3 inline-block">
                                @csrf
                                <x-secondary-button>Decline</x-secondary-button>
                            </form>
                        @endif
                    @endauth
                </div>
            @empty
                <p class="mt-2 text-sm text-gray-600">No quotes yet.</p>
            @endforelse
        </section>
        @if ($job->workOrder)
            <a class="rounded bg-gray-900 px-4 py-2 text-white" href="{{ route('work-orders.show', $job->workOrder) }}">View work order</a>
        @endif
        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Discussion</h3>
            @auth
                <form method="POST" action="{{ route('comments.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="commentable_type" value="job_post">
                    <input type="hidden" name="commentable_id" value="{{ $job->id }}">
                    <x-field name="body" label="Comment" textarea />
                    <x-primary-button>Comment</x-primary-button>
                </form>
            @endauth
            @foreach ($job->comments as $comment)
                <div class="mt-3 rounded bg-gray-50 p-3 text-sm">
                    <p class="font-semibold">{{ $comment->user->name }}</p>
                    <p class="whitespace-pre-line">{{ $comment->body }}</p>
                </div>
            @endforeach
        </section>
    </div>
</x-app-layout>
