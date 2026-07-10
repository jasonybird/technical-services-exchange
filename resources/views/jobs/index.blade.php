<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Jobs"
            description="Browse open work with clear scope signals, technician level, support certification, and risk flags before quoting."
        >
            @auth
                @role('buyer')
                    <a class="rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-700" href="{{ route('jobs.create') }}">Post job</a>
                @endrole
            @endauth
        </x-page-header>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('jobs.index') }}" class="tse-panel p-5">
            <div class="grid gap-3 lg:grid-cols-6">
                <div class="lg:col-span-2">
                    <label for="q" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Search</label>
                    <input id="q" name="q" value="{{ request('q') }}" placeholder="Title, buyer, location, or scope" class="tse-control mt-1 block w-full">
                </div>
                <div>
                    <label for="work_category_id" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Category</label>
                    <select id="work_category_id" name="work_category_id" class="tse-control mt-1 block w-full">
                    <option value="">Any category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) request('work_category_id') === $category->id)>{{ $category->name }}</option>
                    @endforeach
                    </select>
                </div>
                <div>
                    <label for="technician_level" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Technician level</label>
                    <select id="technician_level" name="technician_level" class="tse-control mt-1 block w-full">
                        <option value="">Any tech level</option>
                        @foreach ($technicianLevels as $level => $definition)
                            <option value="{{ $level }}" @selected((int) request('technician_level') === $level)>Up to {{ $definition['short_name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="scope_clarity" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Scope clarity</label>
                    <select id="scope_clarity" name="scope_clarity" class="tse-control mt-1 block w-full">
                        <option value="">Any clarity</option>
                        @foreach (\App\Models\JobPost::SCOPE_CLARITY_STATUSES as $value => $label)
                            <option value="{{ $value }}" @selected(request('scope_clarity') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Status</label>
                    <select id="status" name="status" class="tse-control mt-1 block w-full">
                        <option value="">Any status</option>
                        @foreach (['open', 'assigned', 'closed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', $status) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="support_certified" value="1" @checked(request()->boolean('support_certified')) class="rounded border-slate-300 text-sky-600">
                    Support certified
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="remote_only" value="1" @checked(request()->boolean('remote_only')) class="rounded border-slate-300 text-sky-600">
                    Remote eligible
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="hide_risky" value="1" @checked(request()->boolean('hide_risky')) class="rounded border-slate-300 text-sky-600">
                    Hide risky jobs
                </label>
                <x-primary-button>Filter</x-primary-button>
                <a href="{{ route('jobs.index') }}" class="tse-secondary-action">Reset</a>
            </div>
        </form>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Showing {{ $jobs->firstItem() ?? 0 }}-{{ $jobs->lastItem() ?? 0 }} of {{ $jobs->total() }} jobs.
            </p>
            <div class="flex flex-wrap gap-2">
                @foreach (['q' => 'Search', 'scope_clarity' => 'Clarity', 'status' => 'Status'] as $key => $label)
                    @if (request($key))
                        <x-badge tone="sky">{{ $label }}: {{ str_replace('_', ' ', request($key)) }}</x-badge>
                    @endif
                @endforeach
                @if (request()->boolean('support_certified'))
                    <x-badge tone="emerald">Support certified</x-badge>
                @endif
                @if (request()->boolean('remote_only'))
                    <x-badge tone="sky">Remote eligible</x-badge>
                @endif
                @if (request()->boolean('hide_risky'))
                    <x-badge tone="amber">Risk hidden</x-badge>
                @endif
            </div>
        </div>

        <div class="hidden overflow-hidden rounded border bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:block">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-950 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Job</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Level</th>
                        <th class="px-4 py-3">Schedule</th>
                        <th class="px-4 py-3">Terms</th>
                        <th class="px-4 py-3">Signals</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($jobs as $job)
                        <tr class="align-top">
                            <td class="px-4 py-4">
                                <a href="{{ route('jobs.show', $job) }}" class="font-semibold text-slate-950 hover:text-sky-700 dark:text-white dark:hover:text-sky-300">{{ $job->title }}</a>
                                <p class="mt-1 text-xs text-slate-500">{{ $job->buyer->buyerProfile?->company_name ?? $job->buyer->name }} | {{ $job->location }}</p>
                                <p class="mt-2 line-clamp-2 text-slate-600 dark:text-slate-400">{{ $job->primary_objective ?: $job->scope }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p>{{ $job->workCategory?->name ?? $job->service_category ?? 'Uncategorized' }}</p>
                                <p class="text-xs text-slate-500">{{ $job->workSpecialty?->name }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <x-badge tone="sky">{{ $job->technicianLevel()['short_name'] }}</x-badge>
                            </td>
                            <td class="px-4 py-4">
                                <p>{{ $job->starts_at?->format('M j g:i A') ?? 'No start' }}</p>
                                <p class="text-xs text-slate-500">{{ $job->time_window ?: 'No window' }} | {{ str_replace('_', ' ', $job->work_mode ?: 'onsite') }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p>{{ str_replace('_', ' ', $job->pay_type ?: 'not listed') }}</p>
                                <p class="text-xs text-slate-500">{{ $job->posted_terms_summary ?: $job->payment_terms }}</p>
                                <p class="text-xs text-slate-500">{{ $job->quotes_count }} quote{{ $job->quotes_count === 1 ? '' : 's' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <x-badge tone="{{ $job->scope_clarity_status === 'clear' ? 'emerald' : 'amber' }}">{{ str_replace('_', ' ', $job->scope_clarity_status) }}</x-badge>
                                    <x-badge tone="{{ $job->contact_certified ? 'emerald' : 'rose' }}">{{ $job->contact_certified ? 'Support certified' : 'No support cert' }}</x-badge>
                                    @foreach (array_slice(is_array($job->risk_flags) ? $job->risk_flags : [], 0, 3) as $flag)
                                        <x-badge tone="amber">{{ str_replace('_', ' ', $flag) }}</x-badge>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No jobs matched these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="grid gap-4 lg:hidden">
            @forelse ($jobs as $job)
                <a href="{{ route('jobs.show', $job) }}" class="tse-panel block p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-slate-950 dark:text-white">{{ $job->title }}</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $job->buyer->buyerProfile?->company_name ?? $job->buyer->name }} | {{ $job->location }}</p>
                        </div>
                        <x-badge tone="sky">{{ $job->technicianLevel()['short_name'] }}</x-badge>
                    </div>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($job->primary_objective ?: $job->scope, 180) }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <x-badge tone="{{ $job->scope_clarity_status === 'clear' ? 'emerald' : 'amber' }}">{{ str_replace('_', ' ', $job->scope_clarity_status) }}</x-badge>
                        <x-badge tone="{{ $job->contact_certified ? 'emerald' : 'rose' }}">{{ $job->contact_certified ? 'Support certified' : 'No support cert' }}</x-badge>
                        <x-badge tone="slate">{{ $job->starts_at?->format('M j g:i A') ?? 'No start' }}</x-badge>
                        <x-badge tone="slate">{{ $job->quotes_count }} quote{{ $job->quotes_count === 1 ? '' : 's' }}</x-badge>
                    </div>
                </a>
            @empty
                <x-empty-state title="No jobs matched these filters." description="Try clearing a safety filter, changing the category, or broadening the search." />
            @endforelse
        </div>

        {{ $jobs->links() }}
    </div>
</x-app-layout>
