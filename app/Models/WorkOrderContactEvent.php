<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'work_order_id',
    'user_id',
    'event_type',
    'attempted_channel',
    'attempted_at',
    'result',
    'notes',
])]
class WorkOrderContactEvent extends Model
{
    public const EVENT_TYPES = [
        'contact_failed' => 'Contact failed',
        'support_unavailable' => 'Support unavailable',
        'site_contact_unavailable' => 'Site contact unavailable',
        'contact_reached' => 'Contact reached',
    ];

    protected function casts(): array
    {
        return [
            'attempted_at' => 'datetime',
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
