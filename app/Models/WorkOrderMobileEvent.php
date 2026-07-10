<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'work_order_id',
    'user_id',
    'event_type',
    'payload',
    'latitude',
    'longitude',
    'accuracy_meters',
    'occurred_at',
])]
class WorkOrderMobileEvent extends Model
{
    public const EVENT_TYPES = [
        'status_transition',
        'checklist_update',
        'message_sent',
        'evidence_uploaded',
        'running_late',
        'schedule_update_requested',
        'contact_event',
        'dispute_opened',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'accuracy_meters' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
