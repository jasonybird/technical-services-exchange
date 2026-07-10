<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Notifications"
            description="Control in-app alert categories now, and store explicit email/push intent for future delivery channels."
        />
    </x-slot>
    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <section class="tse-panel p-6">
            <h3 class="font-semibold text-slate-950 dark:text-white">Alert preferences</h3>
            <form method="POST" action="{{ route('notifications.preferences') }}" class="mt-4 space-y-5">
                @csrf
                @method('PATCH')
                <div class="grid gap-3 md:grid-cols-3">
                    <label class="rounded-md border border-slate-200 p-3 text-sm dark:border-slate-800">
                        <input type="checkbox" name="in_app_enabled" value="1" @checked($preference->in_app_enabled) class="rounded border-slate-300 text-sky-600">
                        <span class="ml-2 font-semibold">In-app alerts</span>
                    </label>
                    <label class="rounded-md border border-slate-200 p-3 text-sm dark:border-slate-800">
                        <input type="checkbox" name="email_enabled" value="1" @checked($preference->email_enabled) class="rounded border-slate-300 text-sky-600">
                        <span class="ml-2 font-semibold">Email intent</span>
                    </label>
                    <label class="rounded-md border border-slate-200 p-3 text-sm dark:border-slate-800">
                        <input type="checkbox" name="push_enabled" value="1" @checked($preference->push_enabled) class="rounded border-slate-300 text-sky-600">
                        <span class="ml-2 font-semibold">Push intent</span>
                    </label>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="digest_frequency" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Digest preference</label>
                        <select id="digest_frequency" name="digest_frequency" class="mt-1 block w-full rounded-md border-slate-300 bg-white text-slate-950 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                            @foreach (['immediate' => 'Immediate', 'daily' => 'Daily digest', 'weekly' => 'Weekly digest'] as $value => $label)
                                <option value="{{ $value }}" @selected($preference->digest_frequency === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-field name="quiet_hours_start" label="Quiet hours start" type="time" :value="$preference->quiet_hours_start" />
                        <x-field name="quiet_hours_end" label="Quiet hours end" type="time" :value="$preference->quiet_hours_end" />
                    </div>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Category controls</p>
                    <div class="mt-2 grid gap-3 md:grid-cols-2">
                        @foreach ($eventCategories as $field => $label)
                            <label class="rounded-md border border-slate-200 p-3 text-sm dark:border-slate-800">
                                <input type="checkbox" name="{{ $field }}" value="1" @checked($preference->{$field}) class="rounded border-slate-300 text-sky-600">
                                <span class="ml-2">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <details class="rounded-md border border-slate-200 p-4 dark:border-slate-800">
                    <summary class="cursor-pointer font-semibold text-slate-950 dark:text-white">Fine tune event types</summary>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        @foreach ($eventTypes as $event => $category)
                            <label class="rounded-md border border-slate-200 p-3 text-sm dark:border-slate-800">
                                <input type="checkbox" name="event_preferences[{{ $event }}]" value="1" @checked(($preference->event_preferences[$event] ?? $preference->{$category}) === true) class="rounded border-slate-300 text-sky-600">
                                <span class="ml-2">{{ str_replace('_', ' ', $event) }}</span>
                            </label>
                        @endforeach
                    </div>
                </details>
                <x-primary-button>Save preferences</x-primary-button>
            </form>
        </section>

        <section class="tse-panel p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="font-semibold text-slate-950 dark:text-white">Inbox</h3>
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <x-secondary-button>Mark all read</x-secondary-button>
                </form>
            </div>
        @foreach ($notifications as $notification)
            <div class="mt-4 rounded-md border bg-white p-4 text-sm dark:bg-slate-900 {{ $notification->read_at ? 'border-slate-200 dark:border-slate-800' : 'border-sky-400' }}">
                <p class="font-semibold text-slate-950 dark:text-white">{{ $notification->data['title'] ?? 'Notification' }}</p>
                <p class="mt-1 text-slate-600 dark:text-slate-400">{{ $notification->data['body'] ?? '' }}</p>
                <div class="mt-3 flex gap-3 text-sm">
                    @if (! empty($notification->data['url']))
                        <a class="font-semibold text-sky-700 dark:text-sky-300" href="{{ $notification->data['url'] }}">Open</a>
                    @endif
                    @unless ($notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button class="text-slate-600 dark:text-slate-400" type="submit">Mark read</button>
                        </form>
                    @endunless
                </div>
            </div>
        @endforeach
        {{ $notifications->links() }}
        </section>
    </div>
</x-app-layout>
