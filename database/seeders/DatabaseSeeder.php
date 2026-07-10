<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\JobPost;
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
                'skills' => "POS\nNetworking\nStructured cabling\nPrinter support",
                'tools' => "Laptop\nToner/probe\nCable tester\nBasic hand tools",
                'certifications' => 'Low-voltage and platform-specific certifications can be listed here.',
                'insurance_status' => 'Provider supplied',
                'rate_card' => "Two-hour minimum\nStandard onsite hourly rate set by provider\nTravel negotiated per job",
                'travel_policy' => 'Travel terms are negotiated directly with buyers.',
                'availability_notes' => 'Weekdays and scheduled after-hours work.',
            ]
        );

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
                'vendor_onboarding' => 'Direct vendor onboarding outside the platform.',
                'payment_terms' => 'Direct payment between buyer and provider.',
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
                'location' => 'Tulsa, OK',
                'time_window' => 'Morning',
                'scope' => 'Troubleshoot intermittent POS terminal connectivity.',
                'required_skills' => 'POS and basic networking',
                'required_tools' => 'Laptop, patch cables, toner/probe',
                'deliverables' => "Check in\nPhotos\nResolution notes",
                'payment_terms' => 'Direct buyer/provider payment terms.',
                'vendor_onboarding' => 'Buyer handles direct onboarding.',
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
}
