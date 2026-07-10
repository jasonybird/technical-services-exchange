<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Work Orders"
            description="Accepted quotes become work orders with status tracking, checklists, evidence, reviews, and peer review."
        />
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 px-4 py-8 sm:px-6 lg:px-8">
        <div class="hidden overflow-hidden rounded border bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:block">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Work order</th>
                        <th class="px-4 py-3">Buyer</th>
                        <th class="px-4 py-3">Provider</th>
                        <th class="px-4 py-3">Schedule</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($orders as $order)
                        @php($progress = $order->checklistProgress())
                        <tr class="align-top">
                            <td class="px-4 py-4">
                                <a href="{{ route('work-orders.show', $order) }}" class="font-semibold text-slate-950 hover:text-sky-700 dark:text-white dark:hover:text-sky-300">{{ $order->jobPost->title }}</a>
                                <p class="mt-1 text-xs text-slate-500">#{{ $order->id }}</p>
                            </td>
                            <td class="px-4 py-4">{{ $order->buyer->buyerProfile?->company_name ?? $order->buyer->name }}</td>
                            <td class="px-4 py-4">{{ $order->provider->providerProfile?->business_name ?? $order->provider->name }}</td>
                            <td class="px-4 py-4">
                                <p>{{ $order->scheduled_at?->format('M j, Y g:i A') ?? 'No scheduled time' }}</p>
                                <p class="text-xs text-slate-500">{{ $order->appointment_window ?: 'No appointment window' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <x-badge tone="sky">{{ str_replace('_', ' ', $order->status) }}</x-badge>
                                    <x-badge tone="slate">Checklist {{ $progress['done'] }}/{{ $progress['total'] }}</x-badge>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('work-orders.show', $order) }}" class="text-sm font-semibold text-sky-700 hover:text-sky-900 dark:text-sky-300">Open</a>
                                    <a href="{{ route('work-orders.print', $order) }}" class="text-sm font-semibold text-sky-700 hover:text-sky-900 dark:text-sky-300">Print</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No work orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="grid gap-4 lg:hidden">
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
                <x-empty-state title="No work orders yet." description="Accepted quotes will appear here." />
            @endforelse
        </div>

        {{ $orders->links() }}
    </div>
</x-app-layout>
