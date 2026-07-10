<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'buyer_id', 'title', 'status', 'service_category', 'location', 'starts_at',
    'work_category_id', 'work_specialty_id', 'required_technician_level', 'work_mode',
    'pay_type', 'posted_terms_summary', 'time_window', 'schedule_type', 'remote_eligible', 'scope', 'primary_objective',
    'included_work', 'excluded_work', 'maximum_onsite_expectations', 'expected_duration',
    'required_skills', 'required_tools', 'required_certifications', 'required_safety_gear',
    'deliverables', 'closeout_conditions', 'buyer_provided_equipment',
    'provider_provided_equipment', 'return_shipment_expectations', 'parking_access_notes',
    'onsite_restrictions', 'supplemental_instructions', 'scope_clarity_status',
    'risk_flags', 'payment_terms', 'vendor_onboarding', 'primary_contact_name',
    'primary_contact_phone', 'primary_contact_email', 'backup_contact_name',
    'backup_contact_phone', 'backup_contact_email', 'dispatch_contact_name',
    'dispatch_contact_phone', 'dispatch_contact_email', 'technical_bridge',
    'escalation_contact', 'support_channel', 'support_expected_response_time',
    'support_availability_window', 'contact_certified', 'contact_certified_by_id',
    'contact_certified_at', 'visibility',
])]
class JobPost extends Model
{
    public const SCOPE_CLARITY_STATUSES = [
        'clear' => 'Clear',
        'needs_review' => 'Needs review',
        'broad_scope' => 'Broad scope',
        'missing_support' => 'Missing support',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'required_technician_level' => 'integer',
            'remote_eligible' => 'boolean',
            'risk_flags' => 'array',
            'contact_certified' => 'boolean',
            'contact_certified_at' => 'datetime',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function workCategory(): BelongsTo
    {
        return $this->belongsTo(TaxonomyTerm::class, 'work_category_id');
    }

    public function workSpecialty(): BelongsTo
    {
        return $this->belongsTo(TaxonomyTerm::class, 'work_specialty_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function scopeSnapshot(): array
    {
        return [
            'primary_objective' => $this->primary_objective,
            'included_work' => $this->included_work,
            'excluded_work' => $this->excluded_work,
            'maximum_onsite_expectations' => $this->maximum_onsite_expectations,
            'expected_duration' => $this->expected_duration,
            'required_skills' => $this->required_skills,
            'required_tools' => $this->required_tools,
            'required_certifications' => $this->required_certifications,
            'required_safety_gear' => $this->required_safety_gear,
            'deliverables' => $this->deliverables,
            'closeout_conditions' => $this->closeout_conditions,
            'buyer_provided_equipment' => $this->buyer_provided_equipment,
            'provider_provided_equipment' => $this->provider_provided_equipment,
            'return_shipment_expectations' => $this->return_shipment_expectations,
            'parking_access_notes' => $this->parking_access_notes,
            'onsite_restrictions' => $this->onsite_restrictions,
            'supplemental_instructions' => $this->supplemental_instructions,
            'required_technician_level' => $this->required_technician_level,
            'technician_level' => $this->technicianLevel()['name'],
            'work_category' => $this->workCategory?->name,
            'work_specialty' => $this->workSpecialty?->name,
        ];
    }

    public function contactSnapshot(): array
    {
        return [
            'primary_contact_name' => $this->primary_contact_name,
            'primary_contact_phone' => $this->primary_contact_phone,
            'primary_contact_email' => $this->primary_contact_email,
            'backup_contact_name' => $this->backup_contact_name,
            'backup_contact_phone' => $this->backup_contact_phone,
            'backup_contact_email' => $this->backup_contact_email,
            'dispatch_contact_name' => $this->dispatch_contact_name,
            'dispatch_contact_phone' => $this->dispatch_contact_phone,
            'dispatch_contact_email' => $this->dispatch_contact_email,
            'technical_bridge' => $this->technical_bridge,
            'escalation_contact' => $this->escalation_contact,
            'support_channel' => $this->support_channel,
            'support_expected_response_time' => $this->support_expected_response_time,
            'support_availability_window' => $this->support_availability_window,
            'contact_certified' => $this->contact_certified,
            'contact_certified_at' => $this->contact_certified_at?->toIso8601String(),
        ];
    }

    public function computeRiskFlags(): array
    {
        $flags = [];

        if (! $this->primary_objective || ! $this->included_work || ! $this->excluded_work) {
            $flags[] = 'missing_scope_boundaries';
        }

        if (! $this->deliverables || ! $this->closeout_conditions) {
            $flags[] = 'unclear_closeout';
        }

        if (! $this->backup_contact_phone && ! $this->backup_contact_email) {
            $flags[] = 'missing_contact_backup';
        }

        if (! $this->contact_certified) {
            $flags[] = 'support_not_certified';
        }

        if (strlen((string) $this->supplemental_instructions) > 2000) {
            $flags[] = 'long_supplemental_instructions';
        }

        if ($this->return_shipment_expectations) {
            $flags[] = 'return_shipment_required';
        }

        if ($this->starts_at && $this->starts_at->isBefore(now()->addDay())) {
            $flags[] = 'compressed_schedule';
        }

        if ((int) $this->required_technician_level <= 1 && ($this->required_certifications || str_contains(strtolower((string) $this->primary_objective.' '.$this->included_work), 'troubleshoot'))) {
            $flags[] = 'level_scope_mismatch';
        }

        return array_values(array_unique($flags));
    }

    public function computedScopeClarityStatus(): string
    {
        $flags = $this->computeRiskFlags();

        if (in_array('missing_scope_boundaries', $flags, true) || in_array('unclear_closeout', $flags, true)) {
            return 'needs_review';
        }

        if (in_array('support_not_certified', $flags, true) || in_array('missing_contact_backup', $flags, true)) {
            return 'missing_support';
        }

        if (in_array('long_supplemental_instructions', $flags, true)) {
            return 'broad_scope';
        }

        return 'clear';
    }

    public function technicianLevel(): array
    {
        return config('technician-levels')[(int) $this->required_technician_level] ?? config('technician-levels')[1];
    }
}
