<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Notifications</h2></x-slot>
    <div class="mx-auto max-w-5xl space-y-4 p-6">
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <x-secondary-button>Mark all read</x-secondary-button>
        </form>
        @foreach ($notifications as $notification)
            <div class="rounded border bg-white p-4 {{ $notification->read_at ? '' : 'border-indigo-400' }}">
                <p class="font-semibold">{{ $notification->data['title'] ?? 'Notification' }}</p>
                <p class="mt-1 text-sm text-gray-600">{{ $notification->data['body'] ?? '' }}</p>
                <div class="mt-3 flex gap-3 text-sm">
                    @if (! empty($notification->data['url']))
                        <a class="text-indigo-600" href="{{ $notification->data['url'] }}">Open</a>
                    @endif
                    @unless ($notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button class="text-gray-600" type="submit">Mark read</button>
                        </form>
                    @endunless
                </div>
            </div>
        @endforeach
        {{ $notifications->links() }}
    </div>
</x-app-layout>
