<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Admin</h2></x-slot>
    <div class="mx-auto max-w-7xl space-y-6 p-6">
        <section class="grid gap-4 md:grid-cols-5">
            @foreach ($counts as $label => $count)
                <div class="rounded border bg-white p-4">
                    <p class="text-sm text-gray-500">{{ str_replace('_', ' ', $label) }}</p>
                    <p class="text-2xl font-semibold">{{ $count }}</p>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded border bg-white p-6">
                <h3 class="font-semibold">Recent users</h3>
                @foreach ($users as $user)
                    <p class="mt-3 text-sm">{{ $user->name }} | {{ $user->email }} | {{ $user->roles->pluck('name')->join(', ') }}</p>
                @endforeach
            </div>
            <div class="rounded border bg-white p-6">
                <h3 class="font-semibold">Recent jobs</h3>
                @foreach ($jobs as $job)
                    <p class="mt-3 text-sm"><a class="text-indigo-600" href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a> | {{ $job->status }} | {{ $job->buyer->name }}</p>
                @endforeach
            </div>
            <div class="rounded border bg-white p-6">
                <h3 class="font-semibold">Recent work orders</h3>
                @foreach ($workOrders as $order)
                    <p class="mt-3 text-sm"><a class="text-indigo-600" href="{{ route('work-orders.show', $order) }}">{{ $order->jobPost->title }}</a> | {{ $order->status }}</p>
                @endforeach
            </div>
            <div class="rounded border bg-white p-6">
                <h3 class="font-semibold">Recent disputes</h3>
                @foreach ($disputes as $dispute)
                    <p class="mt-3 text-sm"><a class="text-indigo-600" href="{{ route('disputes.show', $dispute) }}">{{ $dispute->summary }}</a> | {{ $dispute->status }} | {{ $dispute->openedBy->name }}</p>
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
