<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Work Order: {{ $workOrder->jobPost->title }}</h2></x-slot>
    <div class="mx-auto max-w-6xl space-y-6 p-6">
        <section class="rounded border bg-white p-6">
            <p class="text-sm text-gray-500">Status: {{ $workOrder->status }}</p>
            <p class="mt-4 whitespace-pre-line">{{ $workOrder->agreed_terms }}</p>
            <h3 class="mt-6 font-semibold">Deliverables</h3>
            <p class="mt-2 whitespace-pre-line">{{ $workOrder->deliverables_checklist }}</p>
            <form method="POST" action="{{ route('work-orders.transition', $workOrder) }}" class="mt-6 space-y-4">
                @csrf @method('PATCH')
                <label class="block text-sm font-medium">Update status</label>
                <select name="status" class="rounded-md border-gray-300">
                    @foreach (\App\Models\WorkOrder::STATUSES as $status)
                        <option value="{{ $status }}" @selected($workOrder->status === $status)>{{ str_replace('_', ' ', $status) }}</option>
                    @endforeach
                </select>
                <x-field name="completion_notes" label="Completion notes" :value="$workOrder->completion_notes" textarea />
                <x-primary-button>Save status</x-primary-button>
            </form>
        </section>
        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Reviews</h3>
            <form method="POST" action="{{ route('reviews.store', $workOrder) }}" class="mt-4 space-y-4">
                @csrf
                <x-field name="rating" label="Rating 1-5" type="number" />
                <x-field name="body" label="Review" textarea />
                <x-primary-button>Save review</x-primary-button>
            </form>
            @foreach ($workOrder->reviews as $review)
                <div class="mt-4 rounded border p-4 text-sm">
                    <p class="font-semibold">{{ $review->reviewer->name }} rated {{ $review->reviewee->name }} {{ $review->rating }}/5</p>
                    <p class="mt-2 whitespace-pre-line">{{ $review->body }}</p>
                </div>
            @endforeach
        </section>
        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Disputes / Peer Review</h3>
            <form method="POST" action="{{ route('disputes.store', $workOrder) }}" class="mt-4 space-y-4">
                @csrf
                <x-field name="summary" label="Summary" />
                <x-field name="claim" label="Claim" textarea />
                <x-field name="evidence_notes" label="Evidence notes" textarea />
                <x-primary-button>Open dispute</x-primary-button>
            </form>
            @foreach ($workOrder->disputes as $dispute)
                <a href="{{ route('disputes.show', $dispute) }}" class="mt-4 block rounded border p-4 text-sm">
                    <span class="font-semibold">{{ $dispute->summary }}</span> | {{ $dispute->status }}
                </a>
            @endforeach
        </section>
    </div>
</x-app-layout>
