<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Work Order {{ $workOrder->id }} - {{ $workOrder->jobPost->title }}</title>
        <style>
            body { font-family: Arial, sans-serif; color: #111827; margin: 32px; }
            h1, h2 { margin-bottom: 8px; }
            section { border-top: 1px solid #d1d5db; padding-top: 16px; margin-top: 20px; }
            .muted { color: #4b5563; }
            .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
            .box { border: 1px solid #d1d5db; padding: 12px; border-radius: 6px; }
            ul { padding-left: 20px; }
            @media print {
                body { margin: 18mm; }
                button { display: none; }
            }
        </style>
    </head>
    <body>
        <button onclick="window.print()">Print</button>
        <h1>{{ $workOrder->jobPost->title }}</h1>
        <p class="muted">Work Order #{{ $workOrder->id }} | {{ str_replace('_', ' ', $workOrder->status) }}</p>

        <section class="grid">
            <div class="box">
                <h2>Buyer</h2>
                <p>{{ $workOrder->buyer->buyerProfile?->company_name ?? $workOrder->buyer->name }}</p>
            </div>
            <div class="box">
                <h2>Provider</h2>
                <p>{{ $workOrder->provider->providerProfile?->business_name ?? $workOrder->provider->name }}</p>
            </div>
        </section>

        <section>
            <h2>Schedule</h2>
            <p>{{ $workOrder->scheduled_at?->format('M j, Y g:i A') ?? 'Not set' }} | {{ $workOrder->appointment_window ?: 'No window set' }}</p>
        </section>

        <section>
            <h2>Scope Safeguards</h2>
            <p class="muted">Scope clarity: {{ str_replace('_', ' ', $workOrder->scope_clarity_status) }}</p>
            @if (is_array($workOrder->risk_flags) && $workOrder->risk_flags)
                <p class="muted">Risk flags: {{ collect($workOrder->risk_flags)->map(fn ($flag) => str_replace('_', ' ', $flag))->implode(', ') }}</p>
            @endif
            <div class="grid">
                <div class="box">
                    <h3>Primary Objective</h3>
                    <p>{!! nl2br(e($workOrder->scopeSnapshotValue('primary_objective') ?: $workOrder->jobPost->scope)) !!}</p>
                </div>
                <div class="box">
                    <h3>Included Work</h3>
                    <p>{!! nl2br(e($workOrder->scopeSnapshotValue('included_work') ?: 'Not specified')) !!}</p>
                </div>
                <div class="box">
                    <h3>Excluded Work</h3>
                    <p>{!! nl2br(e($workOrder->scopeSnapshotValue('excluded_work') ?: 'Not specified')) !!}</p>
                </div>
                <div class="box">
                    <h3>Closeout Conditions</h3>
                    <p>{!! nl2br(e($workOrder->scopeSnapshotValue('closeout_conditions') ?: 'Not specified')) !!}</p>
                </div>
            </div>
        </section>

        <section>
            <h2>Contact And Support</h2>
            <p class="muted">Certified: {{ $workOrder->contactSnapshotValue('contact_certified') ? 'Yes' : 'No' }}</p>
            <div class="grid">
                <div class="box">
                    <h3>Primary</h3>
                    <p>{{ $workOrder->contactSnapshotValue('primary_contact_name') ?: 'Not set' }}<br>{{ $workOrder->contactSnapshotValue('primary_contact_phone') }} {{ $workOrder->contactSnapshotValue('primary_contact_email') }}</p>
                </div>
                <div class="box">
                    <h3>Backup</h3>
                    <p>{{ $workOrder->contactSnapshotValue('backup_contact_name') ?: 'Not set' }}<br>{{ $workOrder->contactSnapshotValue('backup_contact_phone') }} {{ $workOrder->contactSnapshotValue('backup_contact_email') }}</p>
                </div>
                <div class="box">
                    <h3>Dispatch</h3>
                    <p>{{ $workOrder->contactSnapshotValue('dispatch_contact_name') ?: 'Not set' }}<br>{{ $workOrder->contactSnapshotValue('dispatch_contact_phone') }} {{ $workOrder->contactSnapshotValue('dispatch_contact_email') }}</p>
                </div>
                <div class="box">
                    <h3>Escalation</h3>
                    <p>{{ $workOrder->contactSnapshotValue('escalation_contact') ?: 'Not set' }}<br>{{ $workOrder->contactSnapshotValue('support_channel') }} {{ $workOrder->contactSnapshotValue('support_expected_response_time') }}</p>
                </div>
            </div>
        </section>

        <section>
            <h2>Agreed Terms</h2>
            <p>{!! nl2br(e($workOrder->agreed_terms ?: 'No agreed terms recorded.')) !!}</p>
        </section>

        <section>
            <h2>Checklist</h2>
            <ul>
                @forelse ($workOrder->checklistItems() as $item)
                    <li>{{ (bool) ($workOrder->checklistCompleted()[$item] ?? false) ? '[x]' : '[ ]' }} {{ $item }}</li>
                @empty
                    <li>No checklist items recorded.</li>
                @endforelse
            </ul>
        </section>

        <section>
            <h2>Required Evidence</h2>
            <p>{!! nl2br(e($workOrder->required_evidence ?: 'No required evidence recorded.')) !!}</p>
            @if (is_array($workOrder->evidence_rules) && $workOrder->evidence_rules)
                <ul>
                    @foreach ($workOrder->evidence_rules as $rule)
                        <li>{{ $rule }}</li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section>
            <h2>Change Requests</h2>
            <ul>
                @forelse ($workOrder->changeRequests() as $changeRequest)
                    <li>{{ $changeRequest['summary'] }} | {{ $changeRequest['reason_label'] ?? 'Change' }} | {{ $changeRequest['status'] ?? 'open' }}</li>
                @empty
                    <li>No change requests recorded.</li>
                @endforelse
            </ul>
        </section>

        <section>
            <h2>Contact / Support Events</h2>
            <ul>
                @forelse ($workOrder->contactEvents as $event)
                    <li>{{ \App\Models\WorkOrderContactEvent::EVENT_TYPES[$event->event_type] ?? str_replace('_', ' ', $event->event_type) }} | {{ $event->attempted_at?->format('M j, Y g:i A') ?? $event->created_at->format('M j, Y g:i A') }} | {{ $event->result ?: 'No result' }}</li>
                @empty
                    <li>No contact or support failures recorded.</li>
                @endforelse
            </ul>
        </section>

        <section>
            <h2>Attachments</h2>
            <ul>
                @forelse ($workOrder->attachments as $attachment)
                    <li>{{ $attachment->original_name }} | {{ $attachment->kind }} | {{ $attachment->formattedSize() }}</li>
                @empty
                    <li>No attachments recorded.</li>
                @endforelse
            </ul>
        </section>
    </body>
</html>
