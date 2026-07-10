<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Work Order: {{ $workOrder->jobPost->title }}</h2></x-slot>
    <div class="mx-auto max-w-6xl space-y-6 p-6">
        <section class="rounded border bg-white p-6">
            <p class="text-sm text-gray-500">Status: {{ $workOrder->status }}</p>
            <p class="mt-4 whitespace-pre-line">{{ $workOrder->agreed_terms }}</p>
            <h3 class="mt-6 font-semibold">Deliverables</h3>
            <p class="mt-2 whitespace-pre-line">{{ $workOrder->deliverables_checklist }}</p>
            <x-attachments :attachments="$workOrder->attachments" />
            <x-rating-summary :ratings="$workOrder->ratings" />
            <x-rating-form type="work_order" :id="$workOrder->id" category="work_order_outcome" mode="thumbs" />
            <x-attachment-form type="work_order" :id="$workOrder->id" kind="work_order" />
            <form method="POST" action="{{ route('work-orders.transition', $workOrder) }}" class="mt-6 space-y-4">
                @csrf @method('PATCH')
                <label class="block text-sm font-medium">Update status</label>
                <select name="status" class="rounded-md border-gray-300">
                    @foreach (\App\Models\WorkOrder::ALLOWED_TRANSITIONS[$workOrder->status] ?? [] as $status)
                        <option value="{{ $status }}" @selected($workOrder->status === $status)>{{ str_replace('_', ' ', $status) }}</option>
                    @endforeach
                </select>
                <x-field name="completion_notes" label="Completion notes" :value="$workOrder->completion_notes" textarea />
                <x-primary-button>Save status</x-primary-button>
            </form>
        </section>
        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Messages</h3>
            <form method="POST" action="{{ route('work-order-messages.store', $workOrder) }}" class="mt-4 space-y-4">
                @csrf
                <x-field name="body" label="Message" textarea />
                <x-primary-button>Send message</x-primary-button>
            </form>
            @foreach ($workOrder->messages as $message)
                <div class="mt-3 rounded bg-gray-50 p-3 text-sm">
                    <p class="font-semibold">{{ $message->user->name }} | {{ $message->created_at->diffForHumans() }}</p>
                    <p class="whitespace-pre-line">{{ $message->body }}</p>
                </div>
            @endforeach
        </section>
        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Reviews</h3>
            <form method="POST" action="{{ route('reviews.store', $workOrder) }}" class="mt-4 space-y-4">
                @csrf
                <x-field name="rating" label="Rating 1-5" type="number" />
                <x-field name="communication_rating" label="Communication 1-5" type="number" />
                <x-field name="scope_accuracy_rating" label="Scope accuracy 1-5" type="number" />
                <x-field name="payment_reliability_rating" label="Payment reliability 1-5" type="number" />
                <x-field name="workmanship_rating" label="Workmanship 1-5" type="number" />
                <x-field name="timeliness_rating" label="Timeliness 1-5" type="number" />
                <x-field name="body" label="Review" textarea />
                <x-primary-button>Save review</x-primary-button>
            </form>
            @foreach ($workOrder->reviews as $review)
                <div class="mt-4 rounded border p-4 text-sm">
                    <p class="font-semibold">{{ $review->reviewer->name }} rated {{ $review->reviewee->name }} {{ $review->rating }}/5</p>
                    <p class="mt-1 text-gray-600">Communication {{ $review->communication_rating ?? 'n/a' }} | Scope {{ $review->scope_accuracy_rating ?? 'n/a' }} | Payment {{ $review->payment_reliability_rating ?? 'n/a' }} | Workmanship {{ $review->workmanship_rating ?? 'n/a' }} | Timeliness {{ $review->timeliness_rating ?? 'n/a' }}</p>
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
