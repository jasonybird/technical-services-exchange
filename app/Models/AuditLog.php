<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;

#[Fillable(['actor_id', 'action', 'metadata', 'ip_address', 'user_agent'])]
class AuditLog extends Model
{
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(Request $request, string $action, ?Model $auditable = null, array $metadata = []): self
    {
        $log = new self([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'metadata' => $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        if ($auditable) {
            $log->auditable()->associate($auditable);
        }

        $log->save();

        return $log;
    }
}
