<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Buyers</h2></x-slot>
    <div class="mx-auto max-w-7xl space-y-4 p-6">
        <form method="GET" action="{{ route('buyers.index') }}" class="flex gap-3 rounded border bg-white p-4">
            <input name="q" value="{{ request('q') }}" placeholder="Search buyers..." class="w-full rounded-md border-gray-300">
            <x-primary-button>Search</x-primary-button>
        </form>
        @foreach ($profiles as $profile)
            <a href="{{ route('buyers.show', $profile) }}" class="block rounded border bg-white p-4 shadow-sm">
                <h3 class="font-semibold">{{ $profile->company_name }}</h3>
                <p class="text-sm text-gray-600">{{ $profile->headline }}</p>
                <p class="mt-2 text-sm">{{ $profile->hiring_regions }}</p>
                <x-rating-summary :ratings="$profile->ratings" />
            </a>
        @endforeach
        {{ $profiles->links() }}
    </div>
</x-app-layout>
