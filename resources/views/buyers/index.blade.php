<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Buyers"
            description="Find companies hiring technical providers by category, region, payment terms, contact visibility, and community reputation."
        >
            @auth
                @role('buyer')
                    <a href="{{ route('buyers.edit') }}" class="rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-700">Edit profile</a>
                @endrole
            @endauth
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('buyers.index') }}" class="tse-panel p-5">
            <div class="grid gap-4 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label for="q" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Search</label>
                    <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Company, headline, category, or region" class="tse-control mt-1 block w-full">
                </div>
                <div>
                    <label for="category" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Service category</label>
                    <input id="category" name="category" value="{{ $filters['category'] ?? '' }}" placeholder="Retail, IT, cabling" class="tse-control mt-1 block w-full">
                </div>
                <div>
                    <label for="region" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Hiring region</label>
                    <input id="region" name="region" value="{{ $filters['region'] ?? '' }}" placeholder="Oklahoma, national, Midwest" class="tse-control mt-1 block w-full">
                </div>
                <div>
                    <label for="payment" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Payment terms</label>
                    <input id="payment" name="payment" value="{{ $filters['payment'] ?? '' }}" placeholder="ACH, Net 15, direct" class="tse-control mt-1 block w-full">
                </div>
                <div>
                    <label for="sort" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Sort</label>
                    <select id="sort" name="sort" class="tse-control mt-1 block w-full">
                        <option value="">Newest</option>
                        <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Name</option>
                        <option value="rating" @selected(($filters['sort'] ?? '') === 'rating')>Community rating</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 dark:border-slate-800 dark:text-slate-300">
                        <input type="checkbox" name="public_contact" value="1" @checked(request()->boolean('public_contact')) class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950">
                        Public contact
                    </label>
                </div>
                <div class="flex items-end gap-2">
                    <x-primary-button>Search</x-primary-button>
                    <a href="{{ route('buyers.index') }}" class="tse-secondary-action">Reset</a>
                </div>
            </div>
        </form>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Showing {{ $profiles->firstItem() ?? 0 }}-{{ $profiles->lastItem() ?? 0 }} of {{ $profiles->total() }} buyer profiles.
            </p>
            <div class="flex flex-wrap gap-2">
                @foreach (['q' => 'Search', 'category' => 'Category', 'region' => 'Region', 'payment' => 'Payment'] as $key => $label)
                    @if (! empty($filters[$key]))
                        <x-badge tone="sky">{{ $label }}: {{ $filters[$key] }}</x-badge>
                    @endif
                @endforeach
                @if (request()->boolean('public_contact'))
                    <x-badge tone="emerald">Public contact</x-badge>
                @endif
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($profiles as $profile)
                @php
                    $average = $profile->average_stars ? round($profile->average_stars, 1) : null;
                @endphp
                <a href="{{ route('buyers.show', $profile) }}" class="tse-panel block p-5 transition hover:border-sky-300 hover:shadow-md dark:hover:border-sky-700">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $profile->company_name }}</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $profile->headline ?: 'No headline yet.' }}</p>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ $average ? $average.'/5' : 'No rating' }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $profile->ratings_count }} rating{{ $profile->ratings_count === 1 ? '' : 's' }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($profile->hiring_regions)
                            <x-badge tone="slate">{{ $profile->hiring_regions }}</x-badge>
                        @endif
                        @if ($profile->public_contact)
                            <x-badge tone="emerald">Public contact</x-badge>
                        @endif
                        @if ($profile->payment_terms)
                            <x-badge tone="sky">{{ $profile->payment_terms }}</x-badge>
                        @endif
                        @if ($profile->vendor_onboarding)
                            <x-badge tone="amber">Onboarding notes</x-badge>
                        @endif
                    </div>

                    @if ($profile->service_categories)
                        <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($profile->service_categories, 220) }}</p>
                    @endif
                </a>
            @empty
                <x-empty-state class="lg:col-span-2" title="No buyers matched this search." description="Try removing one filter or broadening the hiring region." />
            @endforelse
        </div>

        {{ $profiles->links() }}
    </div>
</x-app-layout>
