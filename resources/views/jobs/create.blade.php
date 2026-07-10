<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Post Job</h2></x-slot>
    <div class="mx-auto max-w-5xl p-6">
        <form method="POST" action="{{ route('jobs.store') }}" class="space-y-6 rounded border bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            @csrf
            <section class="grid gap-4 md:grid-cols-2">
                <x-field name="title" label="Title" />
                <x-field name="service_category" label="Service category" />
                <x-field name="location" label="Location" />
                <x-field name="starts_at" label="Start date/time" type="datetime-local" />
                <x-field name="time_window" label="Time window" />
                <div>
                    <label for="schedule_type" class="block text-sm font-medium text-slate-800 dark:text-slate-200">Schedule type</label>
                    <select id="schedule_type" name="schedule_type" class="mt-1 block w-full rounded-md border-slate-300 bg-white text-slate-950 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        @foreach (['hard_start' => 'Hard start', 'flex_window' => 'Flexible window', 'appointment' => 'Appointment', 'remote' => 'Remote'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 dark:border-slate-800 dark:text-slate-300">
                    <input type="checkbox" name="remote_eligible" value="1" class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950">
                    Remote eligible
                </label>
            </section>

            <section class="space-y-4">
                <div>
                    <h3 class="font-semibold text-slate-950 dark:text-white">Structured scope</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">These fields define the job. Supplemental instructions cannot override these boundaries.</p>
                </div>
                <x-field name="primary_objective" label="Primary objective" textarea />
                <div class="grid gap-4 md:grid-cols-2">
                    <x-field name="included_work" label="Included work" textarea />
                    <x-field name="excluded_work" label="Excluded work" textarea />
                    <x-field name="maximum_onsite_expectations" label="Maximum onsite expectations" textarea />
                    <x-field name="expected_duration" label="Expected duration" />
                </div>
                <x-field name="scope" label="Legacy summary scope" textarea help="Short summary for compatibility with the first prototype views." />
                <x-field name="supplemental_instructions" label="Supplemental instructions" textarea help="Long instructions are preserved, but they are supplemental and do not create undefined onsite obligations." />
            </section>

            <section class="space-y-4">
                <h3 class="font-semibold text-slate-950 dark:text-white">Requirements and closeout</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-field name="required_skills" label="Required skills" textarea />
                    <x-field name="required_tools" label="Required tools" textarea />
                    <x-field name="required_certifications" label="Required certifications" textarea />
                    <x-field name="required_safety_gear" label="Required safety gear" textarea />
                    <x-field name="deliverables" label="Deliverables checklist" textarea />
                    <x-field name="closeout_conditions" label="Closeout conditions" textarea />
                    <x-field name="buyer_provided_equipment" label="Buyer-provided equipment" textarea />
                    <x-field name="provider_provided_equipment" label="Provider-provided equipment" textarea />
                    <x-field name="return_shipment_expectations" label="Return shipment expectations" textarea />
                    <x-field name="parking_access_notes" label="Parking and access notes" textarea />
                    <x-field name="onsite_restrictions" label="Onsite restrictions" textarea />
                    <x-field name="vendor_onboarding" label="Vendor onboarding" textarea />
                </div>
            </section>

            <section class="space-y-4">
                <div>
                    <h3 class="font-semibold text-slate-950 dark:text-white">Contact and support availability</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Certify real support coverage for the scheduled work window before dispatch.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <x-field name="primary_contact_name" label="Primary contact name" />
                    <x-field name="primary_contact_phone" label="Primary contact phone" />
                    <x-field name="primary_contact_email" label="Primary contact email" type="email" />
                    <x-field name="backup_contact_name" label="Backup contact name" />
                    <x-field name="backup_contact_phone" label="Backup contact phone" />
                    <x-field name="backup_contact_email" label="Backup contact email" type="email" />
                    <x-field name="dispatch_contact_name" label="Dispatch contact name" />
                    <x-field name="dispatch_contact_phone" label="Dispatch contact phone" />
                    <x-field name="dispatch_contact_email" label="Dispatch contact email" type="email" />
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-field name="technical_bridge" label="Technical bridge or meeting link" />
                    <x-field name="escalation_contact" label="Escalation contact" />
                    <x-field name="support_channel" label="Support channel" />
                    <x-field name="support_expected_response_time" label="Expected response time" />
                    <x-field name="support_availability_window" label="Support availability window" />
                    <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 dark:border-slate-800 dark:text-slate-300">
                        <input type="checkbox" name="contact_certified" value="1" class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950">
                        I certify these contacts and support channels will be available during the work window.
                    </label>
                </div>
            </section>

            <x-field name="payment_terms" label="Payment terms" textarea />
            <input type="hidden" name="visibility" value="public">
            <x-primary-button>Post job</x-primary-button>
        </form>
    </div>
</x-app-layout>
