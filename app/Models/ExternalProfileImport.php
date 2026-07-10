<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'provider_profile_id', 'platform', 'external_id', 'profile_url', 'status',
    'rating', 'review_count', 'completed_jobs', 'metrics', 'review_snapshots',
    'notes', 'imported_at',
])]
class ExternalProfileImport extends Model
{
    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'review_snapshots' => 'array',
            'imported_at' => 'datetime',
        ];
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }
}
