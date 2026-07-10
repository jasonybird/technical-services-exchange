<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
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
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 pt-6 sm:pt-0 bg-slate-100 dark:bg-slate-950">
            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="flex items-center gap-3 text-slate-950 dark:text-white">
                    <x-application-logo class="h-10 w-10 fill-current text-sky-600 dark:text-sky-400" />
                    <span class="text-lg font-semibold">Provider Exchange</span>
                </a>
                <x-theme-toggle />
            </div>

            <div class="w-full sm:max-w-md mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white px-6 py-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
