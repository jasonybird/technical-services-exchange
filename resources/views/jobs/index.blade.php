<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Jobs</h2></x-slot>
    <div class="mx-auto max-w-7xl space-y-4 p-6">
        @auth @role('buyer')<a class="rounded bg-indigo-600 px-4 py-2 text-white" href="{{ route('jobs.create') }}">Post job</a>@endrole @endauth
        @foreach ($jobs as $job)
            <a href="{{ route('jobs.show', $job) }}" class="block rounded border bg-white p-4 shadow-sm">
                <h3 class="font-semibold">{{ $job->title }}</h3>
                <p class="text-sm text-gray-600">{{ $job->location }} | {{ $job->service_category }} | {{ $job->status }}</p>
                <p class="mt-2 line-clamp-2 text-sm">{{ $job->scope }}</p>
            </a>
        @endforeach
        {{ $jobs->links() }}
    </div>
</x-app-layout>
