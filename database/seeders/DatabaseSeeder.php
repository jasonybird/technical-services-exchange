<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\JobPost;
use App\Models\TaxonomyTerm;
use App\Models\WorkOrder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $taxonomy = $this->seedTaxonomy();

        $roles = ['admin', 'provider', 'buyer'];

        foreach ($roles as $role) {
            Role::findOrCreate($role);
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => Hash::make('password')]
        );
        $admin->syncRoles(['admin']);

        $provider = User::updateOrCreate(
            ['email' => 'provider@example.com'],
            ['name' => 'Provider User', 'password' => Hash::make('password')]
        );
        $provider->syncRoles(['provider']);

        $buyer = User::updateOrCreate(
            ['email' => 'buyer@example.com'],
            ['name' => 'Buyer User', 'password' => Hash::make('password')]
        );
        $buyer->syncRoles(['buyer']);

        $hybrid = User::updateOrCreate(
            ['email' => 'hybrid@example.com'],
            ['name' => 'Hybrid User', 'password' => Hash::make('password')]
        );
        $hybrid->syncRoles(['provider', 'buyer']);

        foreach ([$admin, $provider, $buyer, $hybrid] as $user) {
            $user->notificationPreference()->firstOrCreate([]);
        }

        $providerProfile = $provider->providerProfile()->updateOrCreate(
            ['user_id' => $provider->id],
            [
                'business_name' => 'Independent Field Tech Demo',
                'headline' => 'POS, networking, cabling, and onsite IT support',
                'bio' => 'Demo provider profile for the technician-owned exchange.',
                'service_area' => 'Central US with regional travel',
                'max_technician_level' => 3,
                'skills' => "POS\nNetworking\nStructured cabling\nPrinter support",
                'services' => [
                    ['name' => 'Smart hands dispatch', 'level' => 'experienced'],
                    ['name' => 'Network troubleshooting', 'level' => 'advanced'],
                    ['name' => 'POS installation', 'level' => 'experienced'],
                ],
                'tools' => "Laptop\nToner/probe\nCable tester\nBasic hand tools",
                'tool_inventory' => [
                    ['name' => 'Laptop', 'category' => 'diagnostics'],
                    ['name' => 'Toner/probe', 'category' => 'cabling'],
                    ['name' => 'Cable tester', 'category' => 'network'],
                ],
                'certifications' => 'Low-voltage and platform-specific certifications can be listed here.',
                'certification_records' => [
                    ['name' => 'Low-voltage certification placeholder', 'issuer' => 'State or authority'],
                ],
                'insurance_status' => 'Provider supplied',
                'rate_card' => "Two-hour minimum\nStandard onsite hourly rate set by provider\nTravel negotiated per job",
                'travel_policy' => 'Travel terms are negotiated directly with buyers.',
                'availability_notes' => 'Weekdays and scheduled after-hours work.',
                'profile_visibility' => [
                    'bio' => true,
                    'services' => true,
                    'tools' => true,
                    'certifications' => true,
                    'rate_card' => true,
                    'availability' => true,
                    'imports' => true,
                ],
            ]
        );

        $providerProfile->taxonomyTerms()->syncWithoutDetaching([
            $taxonomy['categories']['point-of-sale']->id => ['evidence_source' => 'self_declared'],
            $taxonomy['categories']['server-networking']->id => ['evidence_source' => 'self_declared'],
            $taxonomy['specialties']['pos']->id => ['evidence_source' => 'self_declared'],
            $taxonomy['specialties']['wireless-networking']->id => ['evidence_source' => 'self_declared'],
            $taxonomy['skills']['well-tooled-mobile']->id => ['evidence_source' => 'self_declared'],
            $taxonomy['tools']['cable-tester']->id => ['evidence_source' => 'self_declared'],
        ]);

        $providerProfile->externalImports()->updateOrCreate(
            ['platform' => 'Field Nation', 'external_id' => '172-630'],
            [
                'status' => 'manual',
                'notes' => 'Placeholder for imported Field Nation profile/rating history. Public search did not expose a profile by ID.',
                'imported_at' => now(),
            ]
        );

        $buyer->buyerProfile()->updateOrCreate(
            ['user_id' => $buyer->id],
            [
                'company_name' => 'Demo Buyer Services',
                'headline' => 'Transparent field service buyer profile',
                'description' => 'Demo buyer profile showing onboarding and payment terms.',
                'service_categories' => "Networking\nPOS\nPrinter support",
                'hiring_regions' => 'Nationwide',
                'hiring_policies' => [
                    ['name' => 'Direct vendor onboarding', 'summary' => 'Buyer handles vendor setup outside the platform.'],
                    ['name' => 'Clear scope required', 'summary' => 'Work orders should include deliverables and evidence requirements.'],
                ],
                'locations' => [
                    ['name' => 'National dispatch', 'region' => 'United States'],
                ],
                'vendor_onboarding' => 'Direct vendor onboarding outside the platform.',
                'payment_terms' => 'Direct payment between buyer and provider.',
                'profile_visibility' => [
                    'description' => true,
                    'categories' => true,
                    'locations' => true,
                    'policies' => true,
                    'payment_terms' => true,
                    'onboarding' => true,
                ],
            ]
        );

        $provider->socialPosts()->firstOrCreate(
            ['title' => 'Available for regional dispatch'],
            ['body' => 'Demo provider post showing the community feed.', 'visibility' => 'public']
        );

        $job = JobPost::firstOrCreate(
            ['title' => 'Demo POS troubleshooting visit'],
            [
                'buyer_id' => $buyer->id,
                'status' => 'assigned',
                'service_category' => 'POS',
                'work_category_id' => $taxonomy['categories']['point-of-sale']->id,
                'work_specialty_id' => $taxonomy['specialties']['pos']->id,
                'required_technician_level' => 3,
                'work_mode' => 'onsite',
                'pay_type' => 'fixed',
                'posted_terms_summary' => 'Two-hour minimum plus travel if needed',
                'location' => 'Tulsa, OK',
                'time_window' => 'Morning',
                'scope' => 'Troubleshoot intermittent POS terminal connectivity.',
                'primary_objective' => 'Restore intermittent POS terminal connectivity.',
                'included_work' => 'Inspect lane cabling, network path, and terminal connectivity.',
                'excluded_work' => 'No electrical work or ceiling cabling unless accepted as a change request.',
                'closeout_conditions' => 'Buyer validates that the terminal remains online.',
                'required_skills' => 'POS and basic networking',
                'required_tools' => 'Laptop, patch cables, toner/probe',
                'deliverables' => "Check in\nPhotos\nResolution notes",
                'payment_terms' => 'Direct buyer/provider payment terms.',
                'vendor_onboarding' => 'Buyer handles direct onboarding.',
                'contact_certified' => true,
                'scope_clarity_status' => 'clear',
                'risk_flags' => [],
                'visibility' => 'public',
            ]
        );

        $quote = $job->quotes()->firstOrCreate(
            ['provider_id' => $provider->id],
            [
                'status' => 'accepted',
                'requested_amount' => 250,
                'rate_summary' => 'Two-hour minimum plus travel if needed',
                'message' => 'Can handle this visit with standard POS/network tools.',
                'terms' => 'Direct invoice to buyer after completion.',
            ]
        );

        $workOrder = WorkOrder::firstOrCreate(
            ['job_post_id' => $job->id],
            [
                'buyer_id' => $buyer->id,
                'provider_id' => $provider->id,
                'accepted_quote_id' => $quote->id,
                'status' => 'assigned',
                'agreed_terms' => $quote->terms,
                'deliverables_checklist' => $job->deliverables,
                'scheduled_at' => $job->starts_at,
                'appointment_window' => $job->time_window,
                'scope_snapshot' => $job->scopeSnapshot(),
                'contact_snapshot' => $job->contactSnapshot(),
                'scope_clarity_status' => $job->scope_clarity_status,
                'risk_flags' => $job->risk_flags,
                'checklist_items' => ['Check in', 'Photos', 'Resolution notes'],
                'required_evidence' => $job->deliverables,
                'evidence_rules' => ['Arrival photo', 'Completion photo'],
                'status_history' => [['status' => 'assigned', 'user_id' => $buyer->id, 'at' => now()->toIso8601String()]],
            ]
        );

        $workOrder->reviews()->firstOrCreate(
            ['reviewer_id' => $buyer->id, 'reviewee_id' => $provider->id],
            ['rating' => 5, 'body' => 'Demo review record.', 'review_type' => 'buyer_to_provider']
        );

        $workOrder->disputes()->firstOrCreate(
            ['summary' => 'Demo peer review record'],
            [
                'opened_by_id' => $provider->id,
                'status' => 'open',
                'claim' => 'This is a demo dispute record for peer review workflow testing.',
                'evidence_notes' => 'Screenshots, chat, and deliverable notes would be attached here later.',
            ]
        );
    }

    private function seedTaxonomy(): array
    {
        $categories = [
            'access-alarms' => 'Access & Alarms',
            'av-digital-signage' => 'A/V & Digital Signage',
            'cameras' => 'Cameras',
            'ev-equipment' => 'EV Equipment',
            'fiber-cabling' => 'Fiber Cabling',
            'kiosk-atm' => 'Kiosk / ATM',
            'low-voltage-cabling' => 'Low Voltage Cabling',
            'office-equipment' => 'Office Equipment',
            'point-of-sale' => 'Point of Sale',
            'retail-services' => 'Retail Services',
            'server-networking' => 'Server & Networking',
            'telecom' => 'Telecom',
            'other-trades' => 'Other Trades',
        ];

        $specialties = [
            'pos' => ['Point of Sale', 'point-of-sale'],
            'self-checkout' => ['Self-checkout', 'point-of-sale'],
            'networking' => ['Networking', 'server-networking'],
            'wireless-networking' => ['Wireless networking', 'server-networking'],
            'server-storage' => ['Server/storage', 'server-networking'],
            'low-voltage-runs' => ['Low voltage runs', 'low-voltage-cabling'],
            'low-voltage-testing' => ['Low voltage testing', 'low-voltage-cabling'],
            'fiber-testing' => ['Fiber testing', 'fiber-cabling'],
            'pots' => ['POTS', 'telecom'],
            'voip-sip' => ['VoIP/SIP', 'telecom'],
            'digital-signage' => ['Digital signage', 'av-digital-signage'],
            'ip-camera' => ['IP camera', 'cameras'],
            'printer' => ['Printer', 'office-equipment'],
        ];

        $skills = [
            'well-tooled-mobile' => 'Well-tooled mobile',
            'well-prepared' => 'Well-prepared',
            'well-experienced' => 'Well-experienced',
            'smart-hands-ready' => 'Smart-hands ready',
            'independent-installer' => 'Independent installer',
            'field-troubleshooter' => 'Field troubleshooter',
        ];

        $tools = [
            'laptop' => 'Laptop',
            'cable-tester' => 'Cable tester',
            'toner-probe' => 'Toner/probe',
            'labeler' => 'Labeler',
            'basic-hand-tools' => 'Basic hand tools',
        ];

        $certifications = [
            'low-voltage-license' => 'Low-voltage license',
            'comptia-a-plus' => 'CompTIA A+',
            'network-plus' => 'Network+',
            'manufacturer-certification' => 'Manufacturer certification',
        ];

        $categoryRecords = [];
        foreach ($categories as $slug => $name) {
            $categoryRecords[$slug] = TaxonomyTerm::updateOrCreate(
                ['type' => 'work_category', 'slug' => $slug],
                ['name' => $name, 'sort_order' => count($categoryRecords) + 1, 'is_active' => true]
            );
        }

        $specialtyRecords = [];
        foreach ($specialties as $slug => [$name, $parentSlug]) {
            $specialtyRecords[$slug] = TaxonomyTerm::updateOrCreate(
                ['type' => 'work_specialty', 'slug' => $slug],
                ['name' => $name, 'parent_id' => $categoryRecords[$parentSlug]->id ?? null, 'sort_order' => count($specialtyRecords) + 1, 'is_active' => true]
            );
        }

        $skillRecords = [];
        foreach ($skills as $slug => $name) {
            $skillRecords[$slug] = TaxonomyTerm::updateOrCreate(
                ['type' => 'skill', 'slug' => $slug],
                ['name' => $name, 'sort_order' => count($skillRecords) + 1, 'is_active' => true]
            );
        }

        $toolRecords = [];
        foreach ($tools as $slug => $name) {
            $toolRecords[$slug] = TaxonomyTerm::updateOrCreate(
                ['type' => 'tool', 'slug' => $slug],
                ['name' => $name, 'sort_order' => count($toolRecords) + 1, 'is_active' => true]
            );
        }

        $certificationRecords = [];
        foreach ($certifications as $slug => $name) {
            $certificationRecords[$slug] = TaxonomyTerm::updateOrCreate(
                ['type' => 'certification', 'slug' => $slug],
                ['name' => $name, 'sort_order' => count($certificationRecords) + 1, 'is_active' => true]
            );
        }

        return [
            'categories' => $categoryRecords,
            'specialties' => $specialtyRecords,
            'skills' => $skillRecords,
            'tools' => $toolRecords,
            'certifications' => $certificationRecords,
        ];
    }
}
