<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Jobs</h2></x-slot>
    <div class="mx-auto max-w-7xl space-y-4 p-6">
        <form method="GET" action="{{ route('jobs.index') }}" class="rounded border bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <div class="grid gap-3 lg:grid-cols-6">
                <input name="q" value="{{ request('q') }}" placeholder="Search jobs..." class="rounded-md border-gray-300 lg:col-span-2">
                <select name="work_category_id" class="rounded-md border-gray-300">
                    <option value="">Any category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) request('work_category_id') === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="technician_level" class="rounded-md border-gray-300">
                    <option value="">Any tech level</option>
                    @foreach ($technicianLevels as $level => $definition)
                        <option value="{{ $level }}" @selected((int) request('technician_level') === $level)>Up to {{ $definition['short_name'] }}</option>
                    @endforeach
                </select>
                <select name="scope_clarity" class="rounded-md border-gray-300">
                    <option value="">Any clarity</option>
                    @foreach (\App\Models\JobPost::SCOPE_CLARITY_STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(request('scope_clarity') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-md border-gray-300">
                    <option value="">Any status</option>
                    @foreach (['open', 'assigned', 'closed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
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
                <a href="{{ route('jobs.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Reset</a>
            </div>
        </form>
        @auth @role('buyer')<a class="rounded bg-indigo-600 px-4 py-2 text-white" href="{{ route('jobs.create') }}">Post job</a>@endrole @endauth
        <div class="overflow-hidden rounded border bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
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
                                <a href="{{ route('jobs.show', $job) }}" class="font-semibold text-slate-950 hover:text-sky-700 dark:text-white">{{ $job->title }}</a>
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
        {{ $jobs->links() }}
    </div>
</x-app-layout>
