<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Provider Exchange') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <script>
            (function () {
                const stored = localStorage.getItem('tse-theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (stored === 'dark' || (!stored && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans">
        <div class="min-h-screen bg-slate-100 dark:bg-slate-950">
            <header class="border-b border-slate-200 bg-white/90 dark:border-slate-800 dark:bg-slate-900/90">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 text-slate-950 dark:text-white">
                        <x-application-logo class="h-9 w-9 fill-current text-sky-600 dark:text-sky-400" />
                        <div>
                            <p class="text-sm font-semibold leading-4">Provider Exchange</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Technical service network</p>
                        </div>
                    </a>
                    <div class="flex items-center gap-3">
                        <x-theme-toggle />
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-700">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white">Log in</a>
                            <a href="{{ route('register') }}" class="rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-700">Register</a>
                        @endauth
                    </div>
                </div>
            </header>

            <main>
                <section class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1.2fr_.8fr] lg:px-8 lg:py-14">
                    <div class="flex flex-col justify-center">
                        <div class="flex flex-wrap gap-2">
                            <x-badge tone="sky">Provider controlled rates</x-badge>
                            <x-badge tone="emerald">Mutual reputation</x-badge>
                            <x-badge tone="amber">No payment custody</x-badge>
                        </div>
                        <h1 class="mt-5 max-w-3xl text-4xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-5xl">
                            A technical service exchange for independent providers and direct buyers.
                        </h1>
                        <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-400">
                            Build profiles, publish jobs, quote work, manage work orders, and leave visible reputation records without turning the platform into a rate-setting middleman.
                        </p>
                        <div class="mt-7 flex flex-wrap gap-3">
                            <a href="{{ route('providers.index') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200">Browse providers</a>
                            <a href="{{ route('jobs.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm hover:border-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-slate-500">Open jobs</a>
                        </div>
                    </div>

                    <div class="tse-panel p-5">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-white">Current exchange loops</h2>
                        <div class="mt-5 space-y-4">
                            <div class="rounded-md border border-slate-200 p-4 dark:border-slate-800">
                                <p class="text-sm font-semibold text-slate-950 dark:text-white">Profiles</p>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Providers and buyers can present services, terms, coverage, and history.</p>
                            </div>
                            <div class="rounded-md border border-slate-200 p-4 dark:border-slate-800">
                                <p class="text-sm font-semibold text-slate-950 dark:text-white">Jobs and quotes</p>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Buyers post work. Providers answer with their own quoted terms.</p>
                            </div>
                            <div class="rounded-md border border-slate-200 p-4 dark:border-slate-800">
                                <p class="text-sm font-semibold text-slate-950 dark:text-white">Work orders and disputes</p>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Accepted quotes become work orders with evidence, reviews, ratings, and peer dispute signals.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mx-auto grid max-w-7xl gap-4 px-4 pb-12 sm:px-6 md:grid-cols-3 lg:px-8">
                    <x-action-card title="For providers" description="Show your business profile, publish skills and equipment, respond to jobs, and preserve reputation records." :href="route('providers.index')" label="View providers" />
                    <x-action-card title="For buyers" description="Create a visible company profile, post work with clear terms, and review provider responses in one place." :href="route('buyers.index')" label="View buyers" />
                    <x-action-card title="For the community" description="Rate jobs, work orders, buyer profiles, provider profiles, and dispute outcomes so reputation does not flow one way." :href="route('feed.index')" label="Open feed" />
                </section>
            </main>
        </div>
    </body>
</html>
