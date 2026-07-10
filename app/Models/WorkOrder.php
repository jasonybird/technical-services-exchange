<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'job_post_id', 'buyer_id', 'provider_id', 'accepted_quote_id', 'status',
    'status_history', 'agreed_terms', 'deliverables_checklist', 'scheduled_at',
    'appointment_window', 'checklist_items', 'checklist_completed', 'required_evidence',
    'evidence_rules', 'change_requests', 'completion_notes',
    'en_route_at', 'on_site_at', 'started_at', 'completed_at', 'approved_at', 'closed_at',
])]
class WorkOrder extends Model
{
    public const STATUSES = [
        'assigned', 'en_route', 'on_site', 'in_progress', 'completed',
        'buyer_approved', 'disputed', 'closed', 'cancelled',
    ];

    public const ALLOWED_TRANSITIONS = [
        'assigned' => ['en_route', 'cancelled', 'disputed'],
        'en_route' => ['on_site', 'cancelled', 'disputed'],
        'on_site' => ['in_progress', 'completed', 'disputed'],
        'in_progress' => ['completed', 'disputed'],
        'completed' => ['buyer_approved', 'disputed'],
        'buyer_approved' => ['closed', 'disputed'],
        'disputed' => ['closed'],
        'closed' => [],
        'cancelled' => [],
    ];

    protected function casts(): array
    {
        return [
            'status_history' => 'array',
            'scheduled_at' => 'datetime',
            'checklist_items' => 'array',
            'checklist_completed' => 'array',
            'evidence_rules' => 'array',
            'change_requests' => 'array',
            'en_route_at' => 'datetime',
            'on_site_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'approved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function acceptedQuote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'accepted_quote_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WorkOrderMessage::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::ALLOWED_TRANSITIONS[$this->status] ?? [], true);
    }

    public function checklistItems(): array
    {
        if (is_array($this->checklist_items) && $this->checklist_items !== []) {
            return array_values(array_filter($this->checklist_items));
        }

        return collect(preg_split('/\r\n|\r|\n/', (string) $this->deliverables_checklist))
            ->map(fn (string $item): string => trim($item, " \t\n\r\0\x0B-[]"))
            ->filter()
            ->values()
            ->all();
    }

    public function checklistCompleted(): array
    {
        return is_array($this->checklist_completed) ? $this->checklist_completed : [];
    }

    public function checklistProgress(): array
    {
        $items = $this->checklistItems();
        $completed = $this->checklistCompleted();
        $done = collect($items)->filter(fn (string $item): bool => (bool) ($completed[$item] ?? false))->count();

        return [
            'done' => $done,
            'total' => count($items),
        ];
    }

    public function changeRequests(): array
    {
        return is_array($this->change_requests) ? array_values($this->change_requests) : [];
    }
}
