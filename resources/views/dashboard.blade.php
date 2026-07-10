<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Signed in as</p>
                        <p class="font-semibold">{{ auth()->user()->name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Roles</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach (auth()->user()->roles->pluck('name') as $role)
                                <span class="rounded bg-gray-100 px-3 py-1 text-sm">{{ $role }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        @role('provider')
                            <div class="rounded border border-gray-200 p-4">
                                <h3 class="font-semibold">Provider workspace</h3>
                                <p class="mt-2 text-sm text-gray-600">Profile, service areas, rate cards, and work requests will land here.</p>
                            </div>
                        @endrole

                        @role('buyer')
                            <div class="rounded border border-gray-200 p-4">
                                <h3 class="font-semibold">Buyer workspace</h3>
                                <p class="mt-2 text-sm text-gray-600">Company profile, job posts, quotes, and assignments will land here.</p>
                            </div>
                        @endrole

                        @role('admin')
                            <div class="rounded border border-gray-200 p-4">
                                <h3 class="font-semibold">Admin workspace</h3>
                                <p class="mt-2 text-sm text-gray-600">Moderation, role review, and platform controls will land here.</p>
                            </div>
                        @endrole
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
