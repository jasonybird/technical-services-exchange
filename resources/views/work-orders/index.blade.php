<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Work Orders"
            description="Accepted quotes become work orders with status tracking, checklists, evidence, reviews, and peer review."
        />
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 px-4 py-8 sm:px-6 lg:px-8">
        @forelse ($orders as $order)
            @php($progress = $order->checklistProgress())
            <a href="{{ route('work-orders.show', $order) }}" class="tse-panel block p-5 transition hover:border-sky-300 hover:shadow-md dark:hover:border-sky-700">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-950 dark:text-white">{{ $order->jobPost->title }}</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            Buyer: {{ $order->buyer->buyerProfile?->company_name ?? $order->buyer->name }}
                            | Provider: {{ $order->provider->providerProfile?->business_name ?? $order->provider->name }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 sm:justify-end">
                        <x-badge tone="sky">{{ str_replace('_', ' ', $order->status) }}</x-badge>
                        <x-badge tone="slate">Checklist {{ $progress['done'] }}/{{ $progress['total'] }}</x-badge>
                    </div>
                </div>
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                    {{ $order->scheduled_at?->format('M j, Y g:i A') ?? 'No scheduled time' }}
                    @if ($order->appointment_window)
                        | {{ $order->appointment_window }}
                    @endif
                </p>
            </a>
        @empty
            <div class="tse-panel p-8 text-center">
                <h3 class="text-base font-semibold text-slate-950 dark:text-white">No work orders yet.</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Accepted quotes will appear here.</p>
            </div>
        @endforelse

        {{ $orders->links() }}
    </div>
</x-app-layout>
