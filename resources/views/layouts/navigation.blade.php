<nav x-data="{ open: false }" class="border-b border-slate-200 bg-white/95 dark:border-slate-800 dark:bg-slate-900/95">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-slate-950 dark:text-white">
                        <x-application-logo class="block h-9 w-auto fill-current text-sky-600 dark:text-sky-400" />
                        <span class="hidden text-sm font-semibold tracking-wide md:inline">Provider Exchange</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('providers.index')" :active="request()->routeIs('providers.*')">
                        {{ __('Providers') }}
                    </x-nav-link>
                    <x-nav-link :href="route('buyers.index')" :active="request()->routeIs('buyers.*')">
                        {{ __('Buyers') }}
                    </x-nav-link>
                    <x-nav-link :href="route('feed.index')" :active="request()->routeIs('feed.*')">
                        {{ __('Feed') }}
                    </x-nav-link>
                    <x-nav-link :href="route('jobs.index')" :active="request()->routeIs('jobs.*')">
                        {{ __('Jobs') }}
                    </x-nav-link>
                    <x-nav-link :href="route('work-orders.index')" :active="request()->routeIs('work-orders.*')">
                        {{ __('Work Orders') }}
                    </x-nav-link>
                    @auth
                        <x-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                            {{ __('Notifications') }}
                            @if (auth()->user()->unreadNotifications()->count())
                                ({{ auth()->user()->unreadNotifications()->count() }})
                            @endif
                        </x-nav-link>
                    @endauth
                    @role('admin')
                        <x-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.*')">
                            {{ __('Admin') }}
                        </x-nav-link>
                    @endrole
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-3">
                <x-theme-toggle />
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-slate-600 transition duration-150 ease-in-out hover:text-slate-900 focus:outline-none dark:bg-slate-900 dark:text-slate-300 dark:hover:text-white">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            @role('provider')
                                <x-dropdown-link :href="route('providers.edit')">
                                    {{ __('Provider Profile') }}
                                </x-dropdown-link>
                            @endrole
                            @role('buyer')
                                <x-dropdown-link :href="route('buyers.edit')">
                                    {{ __('Buyer Profile') }}
                                </x-dropdown-link>
                            @endrole

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex gap-4 text-sm">
                        <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white">Log in</a>
                        <a href="{{ route('register') }}" class="text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white">Register</a>
                    </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <x-theme-toggle />
                <button @click="open = ! open" class="ms-2 inline-flex items-center justify-center rounded-md p-2 text-slate-500 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-700 focus:bg-slate-100 focus:text-slate-700 focus:outline-none dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:focus:bg-slate-800 dark:focus:text-white">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('providers.index')" :active="request()->routeIs('providers.*')">
                {{ __('Providers') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('buyers.index')" :active="request()->routeIs('buyers.*')">
                {{ __('Buyers') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('feed.index')" :active="request()->routeIs('feed.*')">
                {{ __('Feed') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('jobs.index')" :active="request()->routeIs('jobs.*')">
                {{ __('Jobs') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('work-orders.index')" :active="request()->routeIs('work-orders.*')">
                {{ __('Work Orders') }}
            </x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                    {{ __('Notifications') }}
                </x-responsive-nav-link>
            @endauth
            @auth
                @role('admin')
                    <x-responsive-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.*')">
                        {{ __('Admin') }}
                    </x-responsive-nav-link>
                @endrole
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-slate-200 dark:border-slate-800">
            @auth
                <div class="px-4">
                    <div class="font-medium text-base text-slate-900 dark:text-white">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-slate-500 dark:text-slate-400">{{ Auth::user()->email }}</div>
                </div>
            @endauth

            <div class="mt-3 space-y-1">
                @auth
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>
                    @role('provider')
                        <x-responsive-nav-link :href="route('providers.edit')">
                            {{ __('Provider Profile') }}
                        </x-responsive-nav-link>
                    @endrole
                    @role('buyer')
                        <x-responsive-nav-link :href="route('buyers.edit')">
                            {{ __('Buyer Profile') }}
                        </x-responsive-nav-link>
                    @endrole

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                @else
                    <x-responsive-nav-link :href="route('login')">
                        {{ __('Log in') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')">
                        {{ __('Register') }}
                    </x-responsive-nav-link>
                @endauth
            </div>
        </div>
    </div>
</nav>
