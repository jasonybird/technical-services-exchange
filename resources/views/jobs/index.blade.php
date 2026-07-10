<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Jobs</h2></x-slot>
    <div class="mx-auto max-w-7xl space-y-4 p-6">
        <form method="GET" action="{{ route('jobs.index') }}" class="grid gap-3 rounded border bg-white p-4 md:grid-cols-3">
            <input name="q" value="{{ request('q') }}" placeholder="Search jobs..." class="rounded-md border-gray-300">
            <select name="status" class="rounded-md border-gray-300">
                <option value="">Any status</option>
                @foreach (['open', 'assigned', 'closed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <x-primary-button>Search</x-primary-button>
        </form>
        @auth @role('buyer')<a class="rounded bg-indigo-600 px-4 py-2 text-white" href="{{ route('jobs.create') }}">Post job</a>@endrole @endauth
        @foreach ($jobs as $job)
            <a href="{{ route('jobs.show', $job) }}" class="block rounded border bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="font-semibold">{{ $job->title }}</h3>
                        <p class="text-sm text-gray-600">{{ $job->location }} | {{ $job->service_category }} | {{ $job->status }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-badge tone="{{ $job->scope_clarity_status === 'clear' ? 'emerald' : 'amber' }}">Scope {{ str_replace('_', ' ', $job->scope_clarity_status) }}</x-badge>
                        <x-badge tone="{{ $job->contact_certified ? 'emerald' : 'rose' }}">{{ $job->contact_certified ? 'Support certified' : 'Support not certified' }}</x-badge>
                    </div>
                </div>
                @if (is_array($job->risk_flags) && $job->risk_flags)
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($job->risk_flags as $flag)
                            <x-badge tone="amber">{{ str_replace('_', ' ', $flag) }}</x-badge>
                        @endforeach
                    </div>
                @endif
                <p class="mt-2 line-clamp-2 text-sm">{{ $job->scope }}</p>
                <x-rating-summary :ratings="$job->ratings" />
            </a>
        @endforeach
        {{ $jobs->links() }}
    </div>
</x-app-layout>
