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

        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Reported reviews</h3>
            <p class="mt-1 text-sm text-gray-600">Reported reviews stay visible as moderation records. Hiding a review removes it from normal participant view without deleting the audit trail.</p>
            <div class="mt-4 space-y-4">
                @forelse ($reportedReviews as $review)
                    <div class="rounded border bg-gray-50 p-4 text-sm">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="font-semibold">
                                    <a class="text-indigo-600" href="{{ route('work-orders.show', $review->workOrder) }}">{{ $review->workOrder->jobPost->title }}</a>
                                </p>
                                <p class="mt-1 text-gray-600">
                                    {{ $review->reviewer->name }} rated {{ $review->reviewee->name }} {{ $review->rating }}/5
                                    | reported by {{ $review->reportedBy?->name ?? 'unknown' }}
                                    | {{ $review->reported_at?->diffForHumans() }}
                                </p>
                                <p class="mt-2 whitespace-pre-line text-gray-700">{{ $review->report_reason }}</p>
                            </div>
                            <form method="POST" action="{{ route('reviews.moderate', $review) }}" class="grid min-w-72 gap-2">
                                @csrf
                                @method('PATCH')
                                <label class="text-xs font-semibold text-gray-600" for="moderation_status_{{ $review->id }}">Moderation status</label>
                                <select id="moderation_status_{{ $review->id }}" name="moderation_status" class="rounded-md border-gray-300 text-sm">
                                    @foreach (\App\Models\Review::MODERATION_STATUSES as $status)
                                        <option value="{{ $status }}" @selected($review->moderation_status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <textarea name="moderation_notes" rows="2" class="rounded-md border-gray-300 text-sm" placeholder="Moderation notes">{{ $review->moderation_notes }}</textarea>
                                <x-primary-button>Save moderation</x-primary-button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-600">No reported reviews are waiting for moderation.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
