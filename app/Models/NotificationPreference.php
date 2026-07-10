<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'job_alerts', 'quote_updates', 'work_order_updates',
    'review_updates', 'dispute_updates', 'in_app_enabled', 'email_enabled', 'push_enabled',
    'event_preferences', 'digest_frequency', 'quiet_hours_start', 'quiet_hours_end',
])]
class NotificationPreference extends Model
{
    public const EVENT_CATEGORIES = [
        'job_alerts' => 'Matching jobs',
        'quote_updates' => 'Quote updates',
        'work_order_updates' => 'Work-order activity',
        'review_updates' => 'Reviews and reputation',
        'dispute_updates' => 'Disputes and moderation',
    ];

    public const EVENT_TYPES = [
        'new_matching_job' => 'job_alerts',
        'quote_submitted' => 'quote_updates',
        'quote_revised' => 'quote_updates',
        'quote_declined' => 'quote_updates',
        'quote_accepted' => 'quote_updates',
        'work_order_status' => 'work_order_updates',
        'work_order_change_request' => 'work_order_updates',
        'work_order_message' => 'work_order_updates',
        'contact_event' => 'work_order_updates',
        'running_late' => 'work_order_updates',
        'schedule_update_requested' => 'work_order_updates',
        'provider_tag_verification' => 'review_updates',
        'review_received' => 'review_updates',
        'dispute_opened' => 'dispute_updates',
        'moderation_action' => 'dispute_updates',
    ];

    protected function casts(): array
    {
        return [
            'job_alerts' => 'boolean',
            'quote_updates' => 'boolean',
            'work_order_updates' => 'boolean',
            'review_updates' => 'boolean',
            'dispute_updates' => 'boolean',
            'in_app_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'event_preferences' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function allows(string $eventType, string $channel = 'database'): bool
    {
        if ($channel === 'database' && $this->in_app_enabled === false) {
            return false;
        }

        if ($channel === 'mail' && ! $this->email_enabled) {
            return false;
        }

        if ($channel === 'push' && ! $this->push_enabled) {
            return false;
        }

        $eventPreferences = $this->event_preferences ?? [];
        if (array_key_exists($eventType, $eventPreferences)) {
            return (bool) $eventPreferences[$eventType];
        }

        $category = self::EVENT_TYPES[$eventType] ?? null;

        return $category ? $this->{$category} !== false : true;
    }
}
