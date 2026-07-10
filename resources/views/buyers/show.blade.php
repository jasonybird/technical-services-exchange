<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="{{ $profile->company_name }}"
            :description="$profile->headline"
        />
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <section class="tse-panel p-6">
            <div class="flex flex-wrap gap-2">
                @if ($profile->public_contact)
                    <x-badge tone="emerald">Public contact</x-badge>
                @endif
                @if ($profile->hiring_regions)
                    <x-badge tone="slate">{{ $profile->hiring_regions }}</x-badge>
                @endif
            </div>

            @if ($profile->visible('description'))
                <p class="mt-4 whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $profile->description }}</p>
            @endif

            <dl class="mt-6 grid gap-4 md:grid-cols-2">
                @if ($profile->visible('categories'))
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Service categories</dt><dd class="whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $profile->service_categories }}</dd></div>
                @endif
                @if ($profile->visible('locations'))
                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">Locations</dt>
                        <dd class="mt-2 space-y-2">
                            @forelse ($profile->locations ?? [] as $location)
                                <x-badge tone="slate">{{ $location['name'] }}{{ ! empty($location['region']) ? ' | '.$location['region'] : '' }}</x-badge>
                            @empty
                                <span class="whitespace-pre-line text-sm text-slate-600 dark:text-slate-400">{{ $profile->hiring_regions }}</span>
                            @endforelse
                        </dd>
                    </div>
                @endif
                @if ($profile->visible('policies'))
                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">Hiring policies</dt>
                        <dd class="mt-2 space-y-2">
                            @forelse ($profile->hiring_policies ?? [] as $policy)
                                <div class="rounded-md border border-slate-200 p-3 text-sm dark:border-slate-800">
                                    <p class="font-semibold text-slate-950 dark:text-white">{{ $policy['name'] }}</p>
                                    @if (! empty($policy['summary']))
                                        <p class="mt-1 text-slate-600 dark:text-slate-400">{{ $policy['summary'] }}</p>
                                    @endif
                                </div>
                            @empty
                                <span class="text-sm text-slate-600 dark:text-slate-400">No structured policies listed.</span>
                            @endforelse
                        </dd>
                    </div>
                @endif
                @if ($profile->visible('onboarding'))
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Vendor onboarding</dt><dd class="whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $profile->vendor_onboarding }}</dd></div>
                @endif
                @if ($profile->visible('payment_terms'))
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Payment terms</dt><dd class="whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $profile->payment_terms }}</dd></div>
                @endif
            </dl>

            <x-attachments :attachments="$profile->attachments" />
            <x-rating-summary :ratings="$profile->ratings" />
            <x-rating-form type="buyer_profile" :id="$profile->id" category="buyer_overall" />
            <x-moderation-report-form type="buyer_profile" :id="$profile->id" />
        </section>
    </div>
</x-app-layout>
