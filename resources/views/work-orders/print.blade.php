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
