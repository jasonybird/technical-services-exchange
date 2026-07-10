<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'business_name', 'headline', 'bio', 'service_area', 'skills',
    'services', 'tools', 'tool_inventory', 'certifications', 'certification_records',
    'insurance_status', 'rate_card', 'travel_policy', 'availability_notes',
    'website_url', 'phone', 'public_contact', 'profile_visibility', 'private_notes',
])]
class ProviderProfile extends Model
{
    protected function casts(): array
    {
        return [
            'services' => 'array',
            'tool_inventory' => 'array',
            'certification_records' => 'array',
            'profile_visibility' => 'array',
            'public_contact' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function externalImports(): HasMany
    {
        return $this->hasMany(ExternalProfileImport::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function visible(string $field): bool
    {
        $visibility = $this->profile_visibility ?? [];

        return (bool) ($visibility[$field] ?? true);
    }
}
