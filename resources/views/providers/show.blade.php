<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="{{ $profile->business_name }}"
            :description="$profile->headline"
        />
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <section class="tse-panel p-6">
            <div class="flex flex-wrap gap-2">
                @if ($profile->service_area)
                    <x-badge tone="slate">{{ $profile->service_area }}</x-badge>
                @endif
                @if ($profile->public_contact)
                    <x-badge tone="emerald">Public contact</x-badge>
                @endif
                @if ($profile->insurance_status)
                    <x-badge tone="sky">{{ $profile->insurance_status }}</x-badge>
                @endif
                <x-badge tone="sky">{{ $profile->technicianLevel()['name'] }}</x-badge>
            </div>
            <div class="mt-4 rounded-md border border-sky-200 bg-sky-50 p-4 text-sm text-sky-950 dark:border-sky-900 dark:bg-sky-950/40 dark:text-sky-100">
                <p class="font-semibold">{{ $profile->technicianLevel()['short_name'] }}</p>
                <p class="mt-1">{{ $profile->technicianLevel()['description'] }}</p>
                <p class="mt-2 text-xs">Level and tags are self-declared unless supported by buyer endorsements, completed TSE work, certification proof, or admin verification.</p>
            </div>

            @if ($profile->visible('bio'))
                <p class="mt-4 whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $profile->bio }}</p>
            @endif

            <dl class="mt-6 grid gap-4 md:grid-cols-2">
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Tags and evidence</dt>
                    <dd class="mt-2 flex flex-wrap gap-2">
                        @forelse ($profile->taxonomyTerms as $term)
                            <x-badge tone="slate">{{ $term->name }}{{ $term->pivot?->evidence_source ? ' | '.str_replace('_', ' ', $term->pivot->evidence_source) : '' }}</x-badge>
                        @empty
                            <span class="text-sm text-slate-600 dark:text-slate-400">No taxonomy tags yet.</span>
                        @endforelse
                    </dd>
                </div>
                @if ($profile->visible('services'))
                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">Services</dt>
                        <dd class="mt-2 space-y-2">
                            @forelse ($profile->services ?? [] as $service)
                                <x-badge tone="sky">{{ $service['name'] }}{{ ! empty($service['level']) ? ' | '.$service['level'] : '' }}</x-badge>
                            @empty
                                <span class="text-sm text-slate-600 dark:text-slate-400">{{ $profile->skills }}</span>
                            @endforelse
                        </dd>
                    </div>
                @endif
                @if ($profile->visible('tools'))
                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">Tools</dt>
                        <dd class="mt-2 space-y-2">
                            @forelse ($profile->tool_inventory ?? [] as $tool)
                                <x-badge tone="slate">{{ $tool['name'] }}{{ ! empty($tool['category']) ? ' | '.$tool['category'] : '' }}</x-badge>
                            @empty
                                <span class="whitespace-pre-line text-sm text-slate-600 dark:text-slate-400">{{ $profile->tools }}</span>
                            @endforelse
                        </dd>
                    </div>
                @endif
                @if ($profile->visible('certifications'))
                    <div>
                        <dt class="text-sm text-slate-500 dark:text-slate-400">Certifications</dt>
                        <dd class="mt-2 space-y-2">
                            @forelse ($profile->certification_records ?? [] as $certification)
                                <x-badge tone="amber">{{ $certification['name'] }}{{ ! empty($certification['issuer']) ? ' | '.$certification['issuer'] : '' }}</x-badge>
                            @empty
                                <span class="whitespace-pre-line text-sm text-slate-600 dark:text-slate-400">{{ $profile->certifications }}</span>
                            @endforelse
                        </dd>
                    </div>
                @endif
                @if ($profile->visible('rate_card'))
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Rate card</dt><dd class="whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $profile->rate_card }}</dd></div>
                @endif
                @if ($profile->visible('availability'))
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Travel policy</dt><dd class="whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $profile->travel_policy }}</dd></div>
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Availability</dt><dd class="whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $profile->availability_notes }}</dd></div>
                @endif
            </dl>

            <x-attachments :attachments="$profile->attachments" />
            <x-rating-summary :ratings="$profile->ratings" />
            <x-rating-form type="provider_profile" :id="$profile->id" category="provider_overall" />
        </section>

        @if ($profile->visible('imports'))
            <section class="tse-panel p-6">
                <h3 class="font-semibold text-slate-950 dark:text-white">Imported profile and review history</h3>
                @php
                    $publicImports = $profile->externalImports->filter->publiclyVisible();
                @endphp
                @forelse ($publicImports as $import)
                    <div class="mt-4 rounded-md border border-slate-200 p-4 dark:border-slate-800">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-slate-950 dark:text-white">{{ $import->platform }} {{ $import->external_id ? '#'.$import->external_id : '' }}</p>
                                <x-badge tone="amber">Imported history</x-badge>
                                <x-badge tone="{{ $import->verification_status === 'admin_verified' ? 'emerald' : 'slate' }}">{{ \App\Models\ExternalProfileImport::VERIFICATION_STATUSES[$import->verification_status] ?? $import->verification_status }}</x-badge>
                            </div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Imported marketplace history is provider-controlled context. It is separate from native TSE reputation unless independently verified later.</p>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Rating: {{ $import->rating ?? 'n/a' }} | Reviews: {{ $import->review_count ?? 'n/a' }} | Completed: {{ $import->completed_jobs ?? 'n/a' }}</p>
                            @if ($import->operational_metrics)
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                    Clients: {{ $import->operational_metrics['client_count'] ?? 'n/a' }}
                                    | On-time: {{ $import->operational_metrics['on_time_rate'] ?? 'n/a' }}%
                                    | Backout: {{ $import->operational_metrics['backout_rate'] ?? 'n/a' }}%
                                </p>
                            @endif
                            @if ($import->work_categories)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($import->work_categories as $category)
                                        <x-badge tone="sky">{{ $category }}</x-badge>
                                    @endforeach
                                </div>
                            @endif
                            @if ($import->endorsements)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($import->endorsements as $endorsement)
                                        <x-badge tone="emerald">{{ str_replace('_', ' ', $endorsement) }}</x-badge>
                                    @endforeach
                                </div>
                            @endif
                            @if ($import->canShowSelectedReviews() && $import->selected_reviews)
                                <div class="mt-3 space-y-2">
                                    @foreach ($import->selected_reviews as $review)
                                        <p class="rounded-md bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-950 dark:text-slate-300">{{ $review }}</p>
                                    @endforeach
                                </div>
                            @endif
                            @if ($import->canShowProofAttachments())
                                <x-attachments :attachments="$import->attachments" />
                            @endif
                    </div>
                @empty
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">No public imported-history summaries yet.</p>
                @endforelse
            </section>
        @endif
    </div>
</x-app-layout>
