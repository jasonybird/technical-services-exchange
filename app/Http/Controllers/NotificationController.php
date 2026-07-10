<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\NotificationPreference;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(30),
            'preference' => $request->user()->notificationPreference()->firstOrCreate([]),
            'eventCategories' => NotificationPreference::EVENT_CATEGORIES,
            'eventTypes' => NotificationPreference::EVENT_TYPES,
        ]);
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'job_alerts' => ['nullable', 'boolean'],
            'quote_updates' => ['nullable', 'boolean'],
            'work_order_updates' => ['nullable', 'boolean'],
            'review_updates' => ['nullable', 'boolean'],
            'dispute_updates' => ['nullable', 'boolean'],
            'in_app_enabled' => ['nullable', 'boolean'],
            'email_enabled' => ['nullable', 'boolean'],
            'push_enabled' => ['nullable', 'boolean'],
            'digest_frequency' => ['required', 'string', 'in:immediate,daily,weekly'],
            'quiet_hours_start' => ['nullable', 'date_format:H:i'],
            'quiet_hours_end' => ['nullable', 'date_format:H:i'],
            'event_preferences' => ['nullable', 'array'],
            'event_preferences.*' => ['nullable', 'boolean'],
        ]);

        $events = collect(array_keys(NotificationPreference::EVENT_TYPES))
            ->mapWithKeys(fn (string $event): array => [$event => $request->boolean("event_preferences.$event")])
            ->all();

        $request->user()->notificationPreference()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'job_alerts' => $request->boolean('job_alerts'),
                'quote_updates' => $request->boolean('quote_updates'),
                'work_order_updates' => $request->boolean('work_order_updates'),
                'review_updates' => $request->boolean('review_updates'),
                'dispute_updates' => $request->boolean('dispute_updates'),
                'in_app_enabled' => $request->boolean('in_app_enabled'),
                'email_enabled' => $request->boolean('email_enabled'),
                'push_enabled' => $request->boolean('push_enabled'),
                'event_preferences' => $events,
                'digest_frequency' => $data['digest_frequency'],
                'quiet_hours_start' => $data['quiet_hours_start'] ?? null,
                'quiet_hours_end' => $data['quiet_hours_end'] ?? null,
            ]
        );

        return back()->with('status', 'Notification preferences saved.');
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        return back()->with('status', 'Notification marked read.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'Notifications marked read.');
    }
}
