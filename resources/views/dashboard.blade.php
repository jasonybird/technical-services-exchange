<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Dashboard"
            description="A quick operating view for profiles, jobs, work orders, and reputation activity."
        >
            <a href="{{ route('jobs.create') }}" class="rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-700">Post job</a>
        </x-page-header>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <x-stat-card label="Providers" :value="$counts['providers']" description="Service profiles" />
                <x-stat-card label="Buyers" :value="$counts['buyers']" description="Company profiles" />
                <x-stat-card label="Jobs" :value="$counts['jobs']" description="Open and historical posts" />
                <x-stat-card label="Work orders" :value="$counts['workOrders']" description="Accepted quote records" />
                <x-stat-card label="Disputes" :value="$counts['disputes']" description="Peer review cases" />
            </section>

            <section class="grid gap-4 lg:grid-cols-[.8fr_1.2fr]">
                <div class="tse-panel p-5">
                    <h2 class="text-base font-semibold text-slate-950 dark:text-white">Signed in</h2>
                    <div class="mt-4">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Account</p>
                        <p class="font-semibold text-slate-950 dark:text-white">{{ auth()->user()->name }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">{{ auth()->user()->email }}</p>
                    </div>

                    <div class="mt-5">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Roles</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach (auth()->user()->roles->pluck('name') as $role)
                                <x-badge tone="slate">{{ $role }}</x-badge>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    @role('provider')
                        <x-action-card title="Provider workspace" description="Update services, coverage, equipment, rate cards, and imported profile history." :href="route('providers.edit')" label="Edit profile" />
                    @endrole

                    @role('buyer')
                        <x-action-card title="Buyer workspace" description="Maintain company details, create jobs, review quotes, and manage accepted work." :href="route('buyers.edit')" label="Edit profile" />
                    @endrole

                    @role('admin')
                        <x-action-card title="Admin workspace" description="Review users, moderation surfaces, ratings, disputes, and platform operating signals." :href="route('admin.index')" label="Open admin" />
                    @endrole

                    <x-action-card title="Notifications" description="Review unread platform events for quotes, work orders, reviews, disputes, and profile activity." :href="route('notifications.index')" label="Open inbox" />
                    <x-action-card title="Directory" description="Search providers and buyers without enforcing platform-set rates or preferred pricing." :href="route('providers.index')" label="Browse" />
                    <x-action-card title="Jobs" description="Review posted opportunities, quote history, and accepted work-order paths." :href="route('jobs.index')" label="View jobs" />
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
