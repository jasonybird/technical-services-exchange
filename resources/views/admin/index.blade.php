<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Admin</h2></x-slot>
    <div class="mx-auto max-w-7xl space-y-6 p-6">
        <section class="grid gap-4 md:grid-cols-5">
            @foreach ($counts as $label => $count)
                <div class="rounded border bg-white p-4">
                    <p class="text-sm text-gray-500">{{ str_replace('_', ' ', $label) }}</p>
                    <p class="text-2xl font-semibold">{{ $count }}</p>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded border bg-white p-6">
                <h3 class="font-semibold">Recent users</h3>
                @foreach ($users as $user)
                    <p class="mt-3 text-sm">{{ $user->name }} | {{ $user->email }} | {{ $user->roles->pluck('name')->join(', ') }}</p>
                @endforeach
            </div>
            <div class="rounded border bg-white p-6">
                <h3 class="font-semibold">Recent jobs</h3>
                @foreach ($jobs as $job)
                    <p class="mt-3 text-sm"><a class="text-indigo-600" href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a> | {{ $job->status }} | {{ $job->buyer->name }}</p>
                @endforeach
            </div>
            <div class="rounded border bg-white p-6">
                <h3 class="font-semibold">Recent work orders</h3>
                @foreach ($workOrders as $order)
                    <p class="mt-3 text-sm"><a class="text-indigo-600" href="{{ route('work-orders.show', $order) }}">{{ $order->jobPost->title }}</a> | {{ $order->status }}</p>
                @endforeach
            </div>
            <div class="rounded border bg-white p-6">
                <h3 class="font-semibold">Recent disputes</h3>
                @foreach ($disputes as $dispute)
                    <p class="mt-3 text-sm"><a class="text-indigo-600" href="{{ route('disputes.show', $dispute) }}">{{ $dispute->summary }}</a> | {{ $dispute->status }} | {{ $dispute->openedBy->name }}</p>
                @endforeach
            </div>
        </section>

        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Reported reviews</h3>
            <p class="mt-1 text-sm text-gray-600">Reported reviews stay visible as moderation records. Hiding a review removes it from normal participant view without deleting the audit trail.</p>
            <div class="mt-4 space-y-4">
                @forelse ($reportedReviews as $review)
                    <div class="rounded border bg-gray-50 p-4 text-sm">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="font-semibold">
                                    <a class="text-indigo-600" href="{{ route('work-orders.show', $review->workOrder) }}">{{ $review->workOrder->jobPost->title }}</a>
                                </p>
                                <p class="mt-1 text-gray-600">
                                    {{ $review->reviewer->name }} rated {{ $review->reviewee->name }} {{ $review->rating }}/5
                                    | reported by {{ $review->reportedBy?->name ?? 'unknown' }}
                                    | {{ $review->reported_at?->diffForHumans() }}
                                </p>
                                <p class="mt-2 whitespace-pre-line text-gray-700">{{ $review->report_reason }}</p>
                            </div>
                            <form method="POST" action="{{ route('reviews.moderate', $review) }}" class="grid min-w-72 gap-2">
                                @csrf
                                @method('PATCH')
                                <label class="text-xs font-semibold text-gray-600" for="moderation_status_{{ $review->id }}">Moderation status</label>
                                <select id="moderation_status_{{ $review->id }}" name="moderation_status" class="rounded-md border-gray-300 text-sm">
                                    @foreach (\App\Models\Review::MODERATION_STATUSES as $status)
                                        <option value="{{ $status }}" @selected($review->moderation_status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <textarea name="moderation_notes" rows="2" class="rounded-md border-gray-300 text-sm" placeholder="Moderation notes">{{ $review->moderation_notes }}</textarea>
                                <x-primary-button>Save moderation</x-primary-button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-600">No reported reviews are waiting for moderation.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Imported history verification</h3>
            <p class="mt-1 text-sm text-gray-600">Imported marketplace history is provider-controlled until an admin verifies selected proof. Verification does not merge imported history into native TSE reputation.</p>
            <div class="mt-4 space-y-4">
                @forelse ($importedHistories as $import)
                    <div class="rounded border bg-gray-50 p-4 text-sm">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="font-semibold">
                                    {{ $import->providerProfile->business_name }} | {{ $import->platform }} {{ $import->external_id ? '#'.$import->external_id : '' }}
                                </p>
                                <p class="mt-1 text-gray-600">
                                    {{ \App\Models\ExternalProfileImport::VERIFICATION_STATUSES[$import->verification_status] ?? $import->verification_status }}
                                    | Rating {{ $import->rating ?? 'n/a' }}
                                    | Reviews {{ $import->review_count ?? 'n/a' }}
                                    | Completed {{ $import->completed_jobs ?? 'n/a' }}
                                    | Proof files {{ $import->attachments->count() }}
                                </p>
                                @if ($import->work_categories)
                                    <p class="mt-2 text-gray-700">Categories: {{ implode(', ', $import->work_categories) }}</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('provider-imports.verify', $import) }}" class="grid min-w-72 gap-2">
                                @csrf
                                @method('PATCH')
                                <label class="text-xs font-semibold text-gray-600" for="verification_status_{{ $import->id }}">Verification status</label>
                                <select id="verification_status_{{ $import->id }}" name="verification_status" class="rounded-md border-gray-300 text-sm">
                                    <option value="admin_verified">Admin verified</option>
                                    <option value="needs_more_proof">Needs more proof</option>
                                    <option value="unverified">Unverified</option>
                                </select>
                                <x-primary-button>Save verification</x-primary-button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-600">No imported-history records are waiting for verification.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Moderation reports</h3>
            <p class="mt-1 text-sm text-gray-600">Reports are triage records for profiles, jobs, and attachments. They do not delete or hide content by themselves.</p>
            <div class="mt-4 space-y-4">
                @forelse ($moderationReports as $report)
                    <div class="rounded border bg-gray-50 p-4 text-sm">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="font-semibold">{{ class_basename($report->reportable_type) }} #{{ $report->reportable_id }} | {{ \App\Models\ModerationReport::REASON_CODES[$report->reason_code] ?? $report->reason_code }}</p>
                                <p class="mt-1 text-gray-600">Reported by {{ $report->reporter->name }} | {{ $report->status }} | {{ $report->created_at->diffForHumans() }}</p>
                                <p class="mt-2 whitespace-pre-line text-gray-700">{{ $report->details }}</p>
                            </div>
                            <form method="POST" action="{{ route('moderation-reports.moderate', $report) }}" class="grid min-w-72 gap-2">
                                @csrf
                                @method('PATCH')
                                <label class="text-xs font-semibold text-gray-600" for="report_status_{{ $report->id }}">Report status</label>
                                <select id="report_status_{{ $report->id }}" name="status" class="rounded-md border-gray-300 text-sm">
                                    @foreach (\App\Models\ModerationReport::STATUSES as $status)
                                        <option value="{{ $status }}" @selected($report->status === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                                    @endforeach
                                </select>
                                <textarea name="moderation_notes" rows="2" class="rounded-md border-gray-300 text-sm" placeholder="Moderation notes">{{ $report->moderation_notes }}</textarea>
                                <x-primary-button>Save report</x-primary-button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-600">No moderation reports are waiting.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Recent audit log</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase text-gray-500">
                            <th class="py-2 pr-4">When</th>
                            <th class="py-2 pr-4">Actor</th>
                            <th class="py-2 pr-4">Action</th>
                            <th class="py-2 pr-4">Target</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($auditLogs as $log)
                            <tr>
                                <td class="py-2 pr-4">{{ $log->created_at->format('M j, g:i A') }}</td>
                                <td class="py-2 pr-4">{{ $log->actor?->name ?? 'System' }}</td>
                                <td class="py-2 pr-4">{{ str_replace('.', ' ', $log->action) }}</td>
                                <td class="py-2 pr-4">{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : 'n/a' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-3 text-gray-600">No audit records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
