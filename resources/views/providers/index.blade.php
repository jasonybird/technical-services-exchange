<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Providers"
            description="Find independent technical providers by location, skill, availability, public contact status, and community reputation."
        >
            @auth
                @role('provider')
                    <a href="{{ route('providers.edit') }}" class="rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-700">Edit profile</a>
                @endrole
            @endauth
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('providers.index') }}" class="tse-panel p-5">
            <div class="grid gap-4 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label for="q" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Search</label>
                    <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Business, headline, skill, or area" class="tse-control mt-1 block w-full">
                </div>
                <div>
                    <label for="service_area" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Service area</label>
                    <input id="service_area" name="service_area" value="{{ $filters['service_area'] ?? '' }}" placeholder="Tulsa, Oklahoma, Midwest" class="tse-control mt-1 block w-full">
                </div>
                <div>
                    <label for="skill" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Skill</label>
                    <input id="skill" name="skill" value="{{ $filters['skill'] ?? '' }}" placeholder="POS, network, cabling" class="tse-control mt-1 block w-full">
                </div>
                <div>
                    <label for="technician_level" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Technician level</label>
                    <select id="technician_level" name="technician_level" class="tse-control mt-1 block w-full">
                        <option value="">Any level</option>
                        @foreach ($technicianLevels as $level => $definition)
                            <option value="{{ $level }}" @selected((int) ($filters['technician_level'] ?? 0) === $level)>{{ $definition['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="taxonomy_term_id" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Tag</label>
                    <select id="taxonomy_term_id" name="taxonomy_term_id" class="tse-control mt-1 block w-full">
                        <option value="">Any tag</option>
                        @foreach ($taxonomyTerms as $term)
                            <option value="{{ $term->id }}" @selected((int) ($filters['taxonomy_term_id'] ?? 0) === $term->id)>{{ $term->name }} ({{ str_replace('_', ' ', $term->type) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="insurance" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Insurance</label>
                    <input id="insurance" name="insurance" value="{{ $filters['insurance'] ?? '' }}" placeholder="Insured, COI available" class="tse-control mt-1 block w-full">
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
                    <a href="{{ route('providers.index') }}" class="tse-secondary-action">Reset</a>
                </div>
            </div>
        </form>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Showing {{ $profiles->firstItem() ?? 0 }}-{{ $profiles->lastItem() ?? 0 }} of {{ $profiles->total() }} provider profiles.
            </p>
            <div class="flex flex-wrap gap-2">
                @foreach (['q' => 'Search', 'service_area' => 'Area', 'skill' => 'Skill', 'insurance' => 'Insurance'] as $key => $label)
                    @if (! empty($filters[$key]))
                        <x-badge tone="sky">{{ $label }}: {{ $filters[$key] }}</x-badge>
                    @endif
                @endforeach
                @if (request()->boolean('public_contact'))
                    <x-badge tone="emerald">Public contact</x-badge>
                @endif
                @if (! empty($filters['technician_level']))
                    <x-badge tone="sky">Level: {{ $technicianLevels[(int) $filters['technician_level']]['short_name'] ?? $filters['technician_level'] }}</x-badge>
                @endif
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($profiles as $profile)
                @php
                    $average = $profile->average_stars ? round($profile->average_stars, 1) : null;
                    $imports = $profile->externalImports;
                @endphp
                <a href="{{ route('providers.show', $profile) }}" class="tse-panel block p-5 transition hover:border-sky-300 hover:shadow-md dark:hover:border-sky-700">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $profile->business_name }}</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $profile->headline ?: 'No headline yet.' }}</p>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ $average ? $average.'/5' : 'No rating' }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $profile->ratings_count }} rating{{ $profile->ratings_count === 1 ? '' : 's' }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($profile->service_area)
                            <x-badge tone="slate">{{ $profile->service_area }}</x-badge>
                        @endif
                        <x-badge tone="sky">{{ $profile->technicianLevel()['short_name'] }}</x-badge>
                        @if ($profile->public_contact)
                            <x-badge tone="emerald">Public contact</x-badge>
                        @endif
                        @if ($profile->insurance_status)
                            <x-badge tone="sky">{{ $profile->insurance_status }}</x-badge>
                        @endif
                        @if ($profile->availability_notes)
                            <x-badge tone="amber">Availability noted</x-badge>
                        @endif
                        @if ($imports->isNotEmpty())
                            <x-badge tone="slate">{{ $imports->count() }} imported profile{{ $imports->count() === 1 ? '' : 's' }}</x-badge>
                        @endif
                    </div>

                    @if ($profile->skills)
                        <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($profile->skills, 220) }}</p>
                    @endif
                    @if ($profile->taxonomyTerms->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($profile->taxonomyTerms->take(8) as $term)
                                <x-badge tone="slate">{{ $term->name }}</x-badge>
                            @endforeach
                        </div>
                    @endif
                </a>
            @empty
                <x-empty-state class="lg:col-span-2" title="No providers matched this search." description="Try removing one filter or broadening the service area." />
            @endforelse
        </div>

        {{ $profiles->links() }}
    </div>
</x-app-layout>
