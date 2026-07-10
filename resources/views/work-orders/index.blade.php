<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Work Orders</h2></x-slot>
    <div class="mx-auto max-w-7xl space-y-4 p-6">
        @foreach ($orders as $order)
            <a href="{{ route('work-orders.show', $order) }}" class="block rounded border bg-white p-4 shadow-sm">
                <h3 class="font-semibold">{{ $order->jobPost->title }}</h3>
                <p class="text-sm text-gray-600">{{ $order->status }} | Buyer: {{ $order->buyer->name }} | Provider: {{ $order->provider->name }}</p>
            </a>
        @endforeach
        {{ $orders->links() }}
    </div>
</x-app-layout>
