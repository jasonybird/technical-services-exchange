<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'business_name', 'headline', 'bio', 'service_area', 'skills',
    'tools', 'certifications', 'insurance_status', 'rate_card', 'travel_policy',
    'availability_notes', 'website_url', 'phone', 'public_contact',
])]
class ProviderProfile extends Model
{
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
}
