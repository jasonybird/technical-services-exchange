@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-sky-500 text-sm font-medium leading-5 text-slate-950 focus:outline-none focus:border-sky-700 transition duration-150 ease-in-out dark:text-white'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-500 hover:text-slate-800 hover:border-slate-300 focus:outline-none focus:text-slate-800 focus:border-slate-300 transition duration-150 ease-in-out dark:text-slate-400 dark:hover:text-slate-100 dark:hover:border-slate-600 dark:focus:text-slate-100 dark:focus:border-slate-600';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
