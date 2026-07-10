<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\ProviderProfile;
use App\Models\SocialPost;
use App\Models\TaxonomyTerm;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\ExchangeEventNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExchangeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_and_buyer_profiles_can_be_saved(): void
    {
        $provider = $this->userWithRole('provider');
        $buyer = $this->userWithRole('buyer');

        $this->actingAs($provider)->put('/provider-profile', [
            'business_name' => 'Provider LLC',
            'service_area' => 'Oklahoma',
            'max_technician_level' => 2,
        ])->assertRedirect('/provider-profile');

        $this->actingAs($buyer)->put('/buyer-profile', [
            'company_name' => 'Buyer Inc',
            'payment_terms' => 'Direct ACH',
        ])->assertRedirect('/buyer-profile');

        $this->assertDatabaseHas('provider_profiles', ['business_name' => 'Provider LLC']);
        $this->assertDatabaseHas('buyer_profiles', ['company_name' => 'Buyer Inc']);
    }

    public function test_provider_can_submit_quote_and_buyer_can_create_work_order(): void
    {
        $provider = $this->userWithRole('provider');
        $buyer = $this->userWithRole('buyer');

        $job = JobPost::create([
            'buyer_id' => $buyer->id,
            'title' => 'Install router',
            'location' => 'Tulsa, OK',
            'scope' => 'Install and test router.',
            'visibility' => 'public',
        ]);

        $this->actingAs($provider)->post("/jobs/{$job->id}/quotes", [
            'requested_amount' => 300,
            'rate_summary' => 'Two-hour minimum',
        ])->assertRedirect("/jobs/{$job->id}");

        $quote = $job->quotes()->firstOrFail();

        $this->actingAs($buyer)->post("/quotes/{$quote->id}/accept")
            ->assertRedirect();

        $this->assertDatabaseHas('work_orders', [
            'job_post_id' => $job->id,
            'provider_id' => $provider->id,
            'buyer_id' => $buyer->id,
            'status' => 'assigned',
        ]);
    }

    public function test_work_order_can_transition_review_and_open_dispute(): void
    {
        [$buyer, $provider, $workOrder] = $this->workOrderFixture();

        foreach (['en_route', 'on_site', 'in_progress', 'completed'] as $status) {
            $this->actingAs($provider)->patch("/work-orders/{$workOrder->id}/transition", [
                'status' => $status,
                'completion_notes' => $status === 'completed' ? 'Completed with photos.' : null,
            ])->assertRedirect("/work-orders/{$workOrder->id}");

            $workOrder->refresh();
        }

        $this->actingAs($buyer)->post("/work-orders/{$workOrder->id}/reviews", [
            'rating' => 5,
            'body' => 'Clean work.',
        ])->assertRedirect("/work-orders/{$workOrder->id}");

        $this->actingAs($provider)->post("/work-orders/{$workOrder->id}/disputes", [
            'summary' => 'Scope changed',
            'claim' => 'Additional work was requested onsite.',
        ])->assertRedirect();

        $this->assertDatabaseHas('work_orders', ['id' => $workOrder->id, 'status' => 'disputed']);
        $this->assertDatabaseHas('reviews', ['work_order_id' => $workOrder->id, 'rating' => 5]);
        $this->assertDatabaseHas('disputes', ['work_order_id' => $workOrder->id, 'summary' => 'Scope changed']);
    }

    public function test_reviews_support_category_governance_response_reporting_and_moderation(): void
    {
        [$buyer, $provider, $workOrder] = $this->workOrderFixture();
        $admin = $this->userWithRole('admin');

        $this->actingAs($buyer)->post("/work-orders/{$workOrder->id}/reviews", [
            'rating' => 5,
            'communication_rating' => 5,
            'preparedness_rating' => 4,
            'workmanship_rating' => 5,
            'timeliness_rating' => 5,
            'closeout_quality_rating' => 4,
            'professionalism_rating' => 5,
            'body' => 'Provider completed the work cleanly.',
        ])->assertRedirect("/work-orders/{$workOrder->id}");

        $review = $workOrder->reviews()->firstOrFail();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'review_type' => 'buyer_to_provider',
            'preparedness_rating' => 4,
            'closeout_quality_rating' => 4,
            'moderation_status' => 'published',
        ]);

        $this->actingAs($provider)->post("/reviews/{$review->id}/response", [
            'response_body' => 'Thank you. Scope and closeout were clear.',
        ])->assertRedirect("/work-orders/{$workOrder->id}");

        $this->actingAs($provider)->post("/reviews/{$review->id}/report", [
            'report_reason' => 'Testing moderation workflow.',
        ])->assertRedirect("/work-orders/{$workOrder->id}");

        $review->refresh();
        $this->assertSame('reported', $review->moderation_status);
        $this->assertSame($provider->id, $review->reported_by_id);
        $this->assertNotNull($review->response_at);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('Reported reviews')
            ->assertSee('Testing moderation workflow.');

        $this->actingAs($admin)->patch("/reviews/{$review->id}/moderation", [
            'moderation_status' => 'hidden',
            'moderation_notes' => 'Hidden during review.',
        ])->assertRedirect('/admin');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'moderation_status' => 'hidden',
            'moderated_by_id' => $admin->id,
            'moderation_notes' => 'Hidden during review.',
        ]);
    }

    public function test_review_edit_window_blocks_late_non_admin_edits(): void
    {
        [$buyer, $provider, $workOrder] = $this->workOrderFixture();

        $this->actingAs($buyer)->post("/work-orders/{$workOrder->id}/reviews", [
            'rating' => 4,
            'body' => 'Initial review.',
        ])->assertRedirect("/work-orders/{$workOrder->id}");

        $review = $workOrder->reviews()->firstOrFail();
        $review->forceFill([
            'created_at' => now()->subHours(((int) config('reputation.review_edit_window_hours', 48)) + 1),
        ])->save();

        $this->actingAs($buyer)->post("/work-orders/{$workOrder->id}/reviews", [
            'rating' => 5,
            'body' => 'Late edit.',
        ])->assertStatus(422);

        $this->assertSame('Initial review.', $review->fresh()->body);
    }

    public function test_work_order_details_checklist_change_requests_and_print_view_work(): void
    {
        [$buyer, $provider, $workOrder] = $this->workOrderFixture();

        $this->actingAs($buyer)->patch("/work-orders/{$workOrder->id}/details", [
            'scheduled_at' => '2026-07-12 09:00:00',
            'appointment_window' => '9 AM - 11 AM',
            'agreed_terms' => 'Two-hour minimum.',
            'deliverables_checklist' => "Arrival photo\nCable test\nCloseout notes",
            'required_evidence' => 'Photos and test results required.',
            'evidence_rules' => ['Arrival photo', 'Completion photo'],
        ])->assertRedirect("/work-orders/{$workOrder->id}");

        $workOrder->refresh();

        $this->assertSame(['Arrival photo', 'Cable test', 'Closeout notes'], $workOrder->checklistItems());
        $this->assertSame(['Arrival photo', 'Completion photo'], $workOrder->evidence_rules);

        $this->actingAs($provider)->patch("/work-orders/{$workOrder->id}/transition", [
            'status' => 'en_route',
            'checklist_completed' => [
                'Arrival photo' => '1',
                'Cable test' => '0',
            ],
        ])->assertRedirect("/work-orders/{$workOrder->id}");

        $workOrder->refresh();
        $this->assertTrue($workOrder->checklist_completed['Arrival photo']);
        $this->assertFalse($workOrder->checklist_completed['Cable test']);

        $this->actingAs($provider)->post("/work-orders/{$workOrder->id}/change-requests", [
            'reason_code' => 'scope_expansion',
            'summary' => 'Add second terminal',
            'details' => 'Buyer requested another POS terminal onsite.',
            'scope_impact' => 'Install and test one additional terminal.',
            'schedule_impact' => 'Adds one hour.',
            'terms_impact' => 'Requires added labor approval.',
        ])->assertRedirect("/work-orders/{$workOrder->id}");

        $this->assertDatabaseHas('work_order_change_requests', [
            'work_order_id' => $workOrder->id,
            'reason_code' => 'scope_expansion',
            'summary' => 'Add second terminal',
        ]);
        $this->assertSame('Add second terminal', $workOrder->fresh()->changeRequests()[0]['summary']);

        $this->actingAs($buyer)->get("/work-orders/{$workOrder->id}/print")
            ->assertOk()
            ->assertSee('Arrival photo')
            ->assertSee('Print');
    }

    public function test_scope_and_contact_safeguards_flow_into_work_orders(): void
    {
        $provider = $this->userWithRole('provider');
        $buyer = $this->userWithRole('buyer');

        $this->actingAs($buyer)->post('/jobs', [
            'title' => 'Replace POS lane router',
            'service_category' => 'Point of Sale',
            'required_technician_level' => 3,
            'work_mode' => 'onsite',
            'location' => 'Tulsa, OK',
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'time_window' => '9 AM - 12 PM',
            'schedule_type' => 'flex_window',
            'scope' => 'Replace the router and confirm lane connectivity.',
            'primary_objective' => 'Restore POS lane network connectivity.',
            'included_work' => 'Replace router, connect cables, verify connectivity.',
            'excluded_work' => 'No ceiling cabling or electrical work.',
            'maximum_onsite_expectations' => 'One router replacement and basic test.',
            'expected_duration' => 'Two hours',
            'required_skills' => 'POS networking',
            'required_tools' => 'Laptop and cable tester',
            'deliverables' => "Arrival photo\nCompletion photo",
            'closeout_conditions' => 'Buyer validates lanes online before closeout.',
            'payment_terms' => 'Direct ACH',
            'primary_contact_name' => 'Store Manager',
            'primary_contact_phone' => '555-0100',
            'backup_contact_name' => 'Dispatch',
            'backup_contact_phone' => '555-0101',
            'support_channel' => 'Phone',
            'support_expected_response_time' => '15 minutes',
            'support_availability_window' => 'Full appointment window',
            'contact_certified' => '1',
            'visibility' => 'public',
        ])->assertRedirect();

        $job = JobPost::where('title', 'Replace POS lane router')->firstOrFail();

        $this->assertSame('clear', $job->scope_clarity_status);
        $this->assertTrue($job->contact_certified);
        $this->assertSame([], $job->risk_flags);

        $this->actingAs($provider)->post("/jobs/{$job->id}/quotes", [
            'requested_amount' => 250,
            'rate_summary' => 'Flat project rate',
            'terms' => 'Scope exactly as posted.',
        ])->assertRedirect("/jobs/{$job->id}");

        $quote = $job->quotes()->firstOrFail();

        $this->actingAs($buyer)->post("/quotes/{$quote->id}/accept")
            ->assertRedirect();

        $workOrder = $job->workOrder()->firstOrFail();

        $this->assertSame('Restore POS lane network connectivity.', $workOrder->scope_snapshot['primary_objective']);
        $this->assertSame('Store Manager', $workOrder->contact_snapshot['primary_contact_name']);
        $this->assertSame('clear', $workOrder->scope_clarity_status);

        $this->actingAs($provider)->post("/work-orders/{$workOrder->id}/contact-events", [
            'event_type' => 'support_unavailable',
            'attempted_channel' => 'Phone',
            'result' => 'No answer after two calls',
            'notes' => 'Support bridge did not answer during the certified window.',
        ])->assertRedirect("/work-orders/{$workOrder->id}");

        $this->assertDatabaseHas('work_order_contact_events', [
            'work_order_id' => $workOrder->id,
            'event_type' => 'support_unavailable',
            'result' => 'No answer after two calls',
        ]);

        $this->actingAs($provider)->get("/work-orders/{$workOrder->id}")
            ->assertOk()
            ->assertSee('Support unavailable')
            ->assertSee('Restore POS lane network connectivity.');
    }

    public function test_disputes_and_votes_accept_reason_codes(): void
    {
        [$buyer, $provider, $workOrder] = $this->workOrderFixture();

        $this->actingAs($provider)->post("/work-orders/{$workOrder->id}/disputes", [
            'summary' => 'Could not reach site contact',
            'reason_code' => 'unreachable_contact',
            'claim' => 'The listed contact did not answer.',
            'evidence_notes' => 'Two calls and one text attempted.',
        ])->assertRedirect();

        $dispute = $workOrder->disputes()->firstOrFail();

        $this->actingAs($buyer)->post("/disputes/{$dispute->id}/votes", [
            'recommendation' => 'provider',
            'reason_code' => 'unreachable_contact',
            'reason' => 'The provider documented the contact failure.',
        ])->assertRedirect("/disputes/{$dispute->id}");

        $this->assertDatabaseHas('disputes', [
            'id' => $dispute->id,
            'reason_code' => 'unreachable_contact',
        ]);
        $this->assertDatabaseHas('dispute_votes', [
            'dispute_id' => $dispute->id,
            'reason_code' => 'unreachable_contact',
        ]);
    }

    public function test_external_profile_snapshot_can_be_saved(): void
    {
        $provider = $this->userWithRole('provider');
        $provider->providerProfile()->create(['business_name' => 'Provider LLC']);

        $this->actingAs($provider)->post('/provider-profile/imports', [
            'platform' => 'Field Nation',
            'external_id' => '172-630',
            'visibility' => 'summary',
            'rating' => 4.9,
            'review_count' => 500,
            'completed_jobs' => 1200,
            'client_count' => 75,
            'on_time_rate' => 98,
            'backout_rate' => 1,
            'work_categories_text' => "POS installs\nNetwork troubleshooting",
            'endorsements' => ['communication', 'problem_solving'],
            'selected_reviews_text' => "Reliable and prepared.\nSolved the issue quickly.",
        ])->assertRedirect('/provider-profile');

        $this->assertDatabaseHas('external_profile_imports', [
            'platform' => 'Field Nation',
            'external_id' => '172-630',
            'visibility' => 'summary',
            'verification_status' => 'provider_attested',
        ]);

        $import = $provider->providerProfile->externalImports()->firstOrFail();
        $this->assertSame(['POS installs', 'Network troubleshooting'], $import->work_categories);
        $this->assertSame(['communication', 'problem_solving'], $import->endorsements);
        $this->assertSame(75, $import->operational_metrics['client_count']);
    }

    public function test_imported_history_visibility_and_admin_verification_work(): void
    {
        $provider = $this->userWithRole('provider');
        $admin = $this->userWithRole('admin');
        $profile = $provider->providerProfile()->create([
            'business_name' => 'Import Provider',
            'profile_visibility' => ['imports' => true],
        ]);

        $this->actingAs($provider)->post('/provider-profile/imports', [
            'platform' => 'WorkMarket',
            'visibility' => 'selected_reviews',
            'rating' => 4.8,
            'review_count' => 42,
            'completed_jobs' => 140,
            'selected_reviews_text' => 'Trusted for national rollouts.',
        ])->assertRedirect('/provider-profile');

        $import = $profile->externalImports()->firstOrFail();

        $this->get("/providers/{$profile->id}")
            ->assertOk()
            ->assertSee('WorkMarket')
            ->assertSee('Trusted for national rollouts.')
            ->assertSee('Provider attested');

        $this->actingAs($admin)->patch("/provider-profile/imports/{$import->id}/verify", [
            'verification_status' => 'admin_verified',
        ])->assertRedirect();

        $this->assertDatabaseHas('external_profile_imports', [
            'id' => $import->id,
            'verification_status' => 'admin_verified',
            'verified_by_id' => $admin->id,
        ]);
    }

    public function test_provider_directory_can_filter_and_sort_profiles(): void
    {
        $networkProvider = $this->userWithRole('provider');
        $fiberProvider = $this->userWithRole('provider');

        $networkProfile = ProviderProfile::create([
            'user_id' => $networkProvider->id,
            'business_name' => 'Alpha Network Services',
            'headline' => 'Retail network support',
            'service_area' => 'Tulsa and Oklahoma City',
            'skills' => 'POS, network, router installs',
            'insurance_status' => 'COI available',
            'public_contact' => true,
        ]);

        ProviderProfile::create([
            'user_id' => $fiberProvider->id,
            'business_name' => 'Beta Fiber Works',
            'headline' => 'Fiber contractor',
            'service_area' => 'Kansas City',
            'skills' => 'Fiber splicing',
            'insurance_status' => 'Insured',
            'public_contact' => false,
        ]);

        $this->actingAs($fiberProvider)->post('/ratings', [
            'rateable_type' => 'provider_profile',
            'rateable_id' => $networkProfile->id,
            'category' => 'provider_overall',
            'stars' => 5,
            'body' => 'Strong network work.',
        ])->assertRedirect();

        $this->get('/providers?service_area=Tulsa&skill=network&insurance=COI&public_contact=1&sort=rating')
            ->assertOk()
            ->assertSee('Alpha Network Services')
            ->assertSee('Public contact')
            ->assertSee('5/5')
            ->assertDontSee('Beta Fiber Works');
    }

    public function test_provider_profile_saves_structured_services_tools_certifications_and_visibility(): void
    {
        $provider = $this->userWithRole('provider');

        $this->actingAs($provider)->put('/provider-profile', [
            'business_name' => 'Structured Provider',
            'bio' => 'Public bio.',
            'max_technician_level' => 3,
            'services_text' => "Smart hands | entry\nNetwork troubleshooting | advanced",
            'tool_inventory_text' => "Cable tester | network\nTone probe | cabling",
            'certification_records_text' => "Low voltage license | Oklahoma\nA+ | CompTIA",
            'profile_visibility' => [
                'bio' => '1',
                'services' => '1',
                'tools' => '1',
                'certifications' => '1',
                'imports' => '1',
            ],
            'private_notes' => 'Internal only.',
        ])->assertRedirect('/provider-profile');

        $profile = $provider->providerProfile()->firstOrFail();

        $this->assertSame('Smart hands', $profile->services[0]['name']);
        $this->assertSame('network', $profile->tool_inventory[0]['category']);
        $this->assertSame('CompTIA', $profile->certification_records[1]['issuer']);
        $this->assertFalse($profile->profile_visibility['rate_card']);

        $this->get("/providers/{$profile->id}")
            ->assertOk()
            ->assertSee('Smart hands')
            ->assertSee('Cable tester')
            ->assertSee('Low voltage license')
            ->assertDontSee('Internal only');
    }

    public function test_provider_taxonomy_tags_and_technician_level_can_filter_directory(): void
    {
        $provider = $this->userWithRole('provider');
        $networkTag = TaxonomyTerm::create([
            'type' => 'skill',
            'name' => 'Field troubleshooter',
            'slug' => 'field-troubleshooter',
        ]);

        $this->actingAs($provider)->put('/provider-profile', [
            'business_name' => 'Tagged Provider',
            'service_area' => 'Tulsa',
            'max_technician_level' => 3,
            'skills' => 'Network and POS troubleshooting',
            'taxonomy_terms' => [$networkTag->id],
            'profile_visibility' => [
                'bio' => '1',
                'services' => '1',
                'tools' => '1',
                'certifications' => '1',
                'rate_card' => '1',
                'availability' => '1',
                'imports' => '1',
            ],
        ])->assertRedirect('/provider-profile');

        $profile = $provider->providerProfile()->firstOrFail();

        $this->assertSame(3, $profile->max_technician_level);
        $this->assertDatabaseHas('provider_profile_taxonomy_term', [
            'provider_profile_id' => $profile->id,
            'taxonomy_term_id' => $networkTag->id,
            'evidence_source' => 'self_declared',
        ]);

        $this->get('/providers?technician_level=3&taxonomy_term_id='.$networkTag->id)
            ->assertOk()
            ->assertSee('Tagged Provider')
            ->assertSee('Troubleshooter')
            ->assertSee('Field troubleshooter');
    }

    public function test_buyer_can_verify_provider_tags_after_completed_work(): void
    {
        [$buyer, $provider, $workOrder] = $this->workOrderFixture();
        $workOrder->update(['status' => 'completed']);

        $toolTag = TaxonomyTerm::create([
            'type' => 'tool',
            'name' => 'Cable tester',
            'slug' => 'buyer-endorsed-cable-tester',
        ]);
        $skillTag = TaxonomyTerm::create([
            'type' => 'skill',
            'name' => 'Field troubleshooter',
            'slug' => 'buyer-endorsed-field-troubleshooter',
        ]);
        $profile = $provider->providerProfile()->create([
            'business_name' => 'Verified Provider',
            'max_technician_level' => 3,
            'profile_visibility' => ['imports' => true],
        ]);
        $profile->taxonomyTerms()->sync([
            $toolTag->id => ['evidence_source' => 'self_declared'],
            $skillTag->id => ['evidence_source' => 'self_declared'],
        ]);

        $this->actingAs($buyer)->post("/work-orders/{$workOrder->id}/provider-tag-verification", [
            'level_verdict' => 'confirmed',
            'confirmed_level' => 3,
            'confirmed_term_ids' => [$toolTag->id],
            'disputed_term_ids' => [$skillTag->id],
            'suggested_tags_text' => "Well-prepared\nRetail POS",
            'notes' => 'Provider arrived with the right test gear and solved the issue.',
        ])->assertRedirect("/work-orders/{$workOrder->id}");

        $this->assertDatabaseHas('provider_tag_verifications', [
            'work_order_id' => $workOrder->id,
            'provider_profile_id' => $profile->id,
            'level_verdict' => 'confirmed',
            'confirmed_level' => 3,
            'notes' => 'Provider arrived with the right test gear and solved the issue.',
        ]);
        $this->assertDatabaseHas('provider_profile_taxonomy_term', [
            'provider_profile_id' => $profile->id,
            'taxonomy_term_id' => $toolTag->id,
            'evidence_source' => 'buyer_endorsed',
        ]);
        $this->assertDatabaseHas('provider_profile_taxonomy_term', [
            'provider_profile_id' => $profile->id,
            'taxonomy_term_id' => $skillTag->id,
            'evidence_source' => 'self_declared',
        ]);

        $this->actingAs($buyer)->get("/work-orders/{$workOrder->id}")
            ->assertOk()
            ->assertSee('Provider tag verification')
            ->assertSee('Cable tester')
            ->assertSee('Well-prepared');

        $this->get("/providers/{$profile->id}")
            ->assertOk()
            ->assertSee('Completed-work competency evidence')
            ->assertSee('Cable tester')
            ->assertSee('Retail POS')
            ->assertSee('buyer endorsed');
    }

    public function test_provider_tag_verification_rejects_provider_and_uncompleted_work(): void
    {
        [$buyer, $provider, $workOrder] = $this->workOrderFixture();
        $provider->providerProfile()->create([
            'business_name' => 'Unready Provider',
            'max_technician_level' => 2,
        ]);

        $this->actingAs($provider)->post("/work-orders/{$workOrder->id}/provider-tag-verification", [
            'level_verdict' => 'confirmed',
            'confirmed_level' => 2,
        ])->assertForbidden();

        $this->actingAs($buyer)->post("/work-orders/{$workOrder->id}/provider-tag-verification", [
            'level_verdict' => 'confirmed',
            'confirmed_level' => 2,
        ])->assertStatus(422);

        $this->assertDatabaseCount('provider_tag_verifications', 0);
    }

    public function test_buyer_directory_can_filter_and_sort_profiles(): void
    {
        $retailBuyer = $this->userWithRole('buyer');
        $warehouseBuyer = $this->userWithRole('buyer');

        $retailProfile = $retailBuyer->buyerProfile()->create([
            'company_name' => 'Alpha Retail Dispatch',
            'headline' => 'Nationwide retail IT',
            'service_categories' => 'Retail IT, POS, networking',
            'hiring_regions' => 'Oklahoma and Texas',
            'payment_terms' => 'Direct ACH Net 15',
            'vendor_onboarding' => 'W9 and COI required',
            'public_contact' => true,
        ]);

        $warehouseBuyer->buyerProfile()->create([
            'company_name' => 'Beta Warehouse Group',
            'headline' => 'Warehouse projects',
            'service_categories' => 'Low voltage',
            'hiring_regions' => 'Michigan',
            'payment_terms' => 'Paper check',
            'public_contact' => false,
        ]);

        $this->actingAs($warehouseBuyer)->post('/ratings', [
            'rateable_type' => 'buyer_profile',
            'rateable_id' => $retailProfile->id,
            'category' => 'buyer_overall',
            'stars' => 4,
            'body' => 'Clear terms.',
        ])->assertRedirect();

        $this->get('/buyers?category=POS&region=Oklahoma&payment=ACH&public_contact=1&sort=rating')
            ->assertOk()
            ->assertSee('Alpha Retail Dispatch')
            ->assertSee('Public contact')
            ->assertSee('4/5')
            ->assertDontSee('Beta Warehouse Group');
    }

    public function test_buyer_profile_saves_structured_hiring_policies_locations_and_visibility(): void
    {
        $buyer = $this->userWithRole('buyer');

        $this->actingAs($buyer)->put('/buyer-profile', [
            'company_name' => 'Structured Buyer',
            'description' => 'Buyer description.',
            'hiring_policies_text' => "Direct ACH | Net 15 after approval\nCOI required | Before dispatch",
            'locations_text' => "Tulsa office | Oklahoma\nDallas office | Texas",
            'profile_visibility' => [
                'description' => '1',
                'locations' => '1',
                'policies' => '1',
            ],
            'private_notes' => 'Buyer internal note.',
        ])->assertRedirect('/buyer-profile');

        $profile = $buyer->buyerProfile()->firstOrFail();

        $this->assertSame('Direct ACH', $profile->hiring_policies[0]['name']);
        $this->assertSame('Texas', $profile->locations[1]['region']);
        $this->assertFalse($profile->profile_visibility['payment_terms']);

        $this->get("/buyers/{$profile->id}")
            ->assertOk()
            ->assertSee('Direct ACH')
            ->assertSee('Tulsa office')
            ->assertDontSee('Buyer internal note');
    }

    public function test_available_work_board_filters_by_category_level_and_safety_signals(): void
    {
        $buyer = $this->userWithRole('buyer');
        $category = TaxonomyTerm::create([
            'type' => 'work_category',
            'name' => 'Point of Sale',
            'slug' => 'point-of-sale',
        ]);

        JobPost::create([
            'buyer_id' => $buyer->id,
            'title' => 'Clear POS troubleshooting',
            'status' => 'open',
            'service_category' => 'POS',
            'work_category_id' => $category->id,
            'required_technician_level' => 3,
            'work_mode' => 'onsite',
            'location' => 'Tulsa, OK',
            'scope' => 'Troubleshoot POS issue.',
            'primary_objective' => 'Find POS connectivity fault.',
            'included_work' => 'Test cabling and terminal connectivity.',
            'excluded_work' => 'No electrical work.',
            'deliverables' => 'Photos and notes',
            'closeout_conditions' => 'Buyer validates terminal online.',
            'contact_certified' => true,
            'scope_clarity_status' => 'clear',
            'risk_flags' => [],
            'visibility' => 'public',
        ]);

        JobPost::create([
            'buyer_id' => $buyer->id,
            'title' => 'Risky smart hands dump',
            'status' => 'open',
            'service_category' => 'Other',
            'required_technician_level' => 1,
            'work_mode' => 'onsite',
            'location' => 'OKC, OK',
            'scope' => 'Do everything onsite.',
            'scope_clarity_status' => 'needs_review',
            'risk_flags' => ['missing_scope_boundaries'],
            'visibility' => 'public',
        ]);

        $this->get('/jobs?work_category_id='.$category->id.'&technician_level=3&scope_clarity=clear&support_certified=1&hide_risky=1')
            ->assertOk()
            ->assertSee('Clear POS troubleshooting')
            ->assertSee('Troubleshooter')
            ->assertDontSee('Risky smart hands dump');
    }

    public function test_invalid_work_order_transition_is_rejected(): void
    {
        [, $provider, $workOrder] = $this->workOrderFixture();

        $this->actingAs($provider)->patch("/work-orders/{$workOrder->id}/transition", [
            'status' => 'completed',
        ])->assertStatus(422);
    }

    public function test_comments_messages_dispute_votes_and_uploads_work(): void
    {
        Storage::fake('public');

        [$buyer, $provider, $workOrder] = $this->workOrderFixture();
        $post = SocialPost::create([
            'user_id' => $provider->id,
            'body' => 'Available for work.',
            'visibility' => 'public',
        ]);

        $this->actingAs($buyer)->post('/comments', [
            'commentable_type' => 'social_post',
            'commentable_id' => $post->id,
            'body' => 'Good to know.',
        ])->assertRedirect();

        $this->actingAs($buyer)->post("/work-orders/{$workOrder->id}/messages", [
            'body' => 'Please upload completion photos.',
        ])->assertRedirect("/work-orders/{$workOrder->id}");

        $this->actingAs($provider)->post('/attachments', [
            'attachable_type' => 'work_order',
            'attachable_id' => $workOrder->id,
            'caption' => 'Completion photo',
            'file' => UploadedFile::fake()->image('complete.jpg'),
        ])->assertRedirect();

        $response = $this->actingAs($provider)->post("/work-orders/{$workOrder->id}/disputes", [
            'summary' => 'Scope changed',
            'claim' => 'Additional scope was requested.',
        ])->assertRedirect();

        $dispute = $workOrder->disputes()->firstOrFail();

        $this->actingAs($buyer)->post("/disputes/{$dispute->id}/votes", [
            'recommendation' => 'split',
            'reason' => 'Both sides have partial documentation.',
        ])->assertRedirect("/disputes/{$dispute->id}");

        $this->assertDatabaseHas('comments', ['body' => 'Good to know.']);
        $this->assertDatabaseHas('work_order_messages', ['body' => 'Please upload completion photos.']);
        $this->assertDatabaseHas('attachments', ['caption' => 'Completion photo']);
        $this->assertDatabaseHas('dispute_votes', ['recommendation' => 'split']);
    }

    public function test_attachment_uploads_follow_storage_policy_and_can_be_deleted(): void
    {
        Storage::fake('public');
        config()->set('provider-exchange.attachments.disk', 'public');
        config()->set('provider-exchange.attachments.root', 'test-assets');
        config()->set('provider-exchange.attachments.max_kb', 1024);
        config()->set('provider-exchange.attachments.allowed_mime_types', ['image/jpeg', 'image/png']);

        $provider = $this->userWithRole('provider');
        $profile = $provider->providerProfile()->create(['business_name' => 'Asset Provider']);

        $this->actingAs($provider)->post('/attachments', [
            'attachable_type' => 'provider_profile',
            'attachable_id' => $profile->id,
            'kind' => 'profile',
            'caption' => 'Profile asset',
            'file' => UploadedFile::fake()->image('profile.jpg'),
        ])->assertRedirect();

        $attachment = $profile->attachments()->firstOrFail();

        $this->assertSame('public', $attachment->disk);
        $this->assertStringStartsWith('test-assets/provider-profile/', $attachment->path);
        Storage::disk('public')->assertExists($attachment->path);

        $this->actingAs($provider)->delete("/attachments/{$attachment->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
        Storage::disk('public')->assertMissing($attachment->path);
    }

    public function test_attachment_upload_rejects_disallowed_mime_types(): void
    {
        Storage::fake('public');
        config()->set('provider-exchange.attachments.allowed_mime_types', ['image/jpeg']);

        $provider = $this->userWithRole('provider');
        $profile = $provider->providerProfile()->create(['business_name' => 'Mime Provider']);

        $this->actingAs($provider)->from('/provider-profile')->post('/attachments', [
            'attachable_type' => 'provider_profile',
            'attachable_id' => $profile->id,
            'kind' => 'profile',
            'file' => UploadedFile::fake()->create('payload.zip', 1, 'application/zip'),
        ])->assertRedirect('/provider-profile')
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('attachments', 0);
    }

    public function test_everything_rating_layer_accepts_profile_and_work_order_ratings(): void
    {
        [$buyer, $provider, $workOrder] = $this->workOrderFixture();
        $profile = ProviderProfile::create([
            'user_id' => $provider->id,
            'business_name' => 'Rated Provider',
        ]);

        $this->actingAs($buyer)->post('/ratings', [
            'rateable_type' => 'provider_profile',
            'rateable_id' => $profile->id,
            'category' => 'provider_overall',
            'stars' => 5,
            'body' => 'Reliable provider.',
        ])->assertRedirect();

        $this->actingAs($provider)->post('/ratings', [
            'rateable_type' => 'work_order',
            'rateable_id' => $workOrder->id,
            'category' => 'work_order_outcome',
            'thumbs_up' => 1,
            'body' => 'Good job outcome.',
        ])->assertRedirect();

        $this->assertDatabaseHas('ratings', ['category' => 'provider_overall', 'stars' => 5]);
        $this->assertDatabaseHas('ratings', ['category' => 'work_order_outcome', 'thumbs_up' => true]);
    }

    public function test_notification_inbox_lists_and_marks_notifications_read(): void
    {
        $user = $this->userWithRole('provider');

        $user->notify(new ExchangeEventNotification('Test notice', 'Body text', '/jobs', 'test'));

        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)->get('/notifications')
            ->assertOk()
            ->assertSee('Test notice');

        $this->actingAs($user)->post("/notifications/{$notification->id}/read")
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function workOrderFixture(): array
    {
        $provider = $this->userWithRole('provider');
        $buyer = $this->userWithRole('buyer');
        $job = JobPost::create([
            'buyer_id' => $buyer->id,
            'title' => 'Repair printer',
            'location' => 'OKC, OK',
            'scope' => 'Repair printer.',
            'visibility' => 'public',
        ]);

        $workOrder = WorkOrder::create([
            'job_post_id' => $job->id,
            'buyer_id' => $buyer->id,
            'provider_id' => $provider->id,
            'status' => 'assigned',
        ]);

        return [$buyer, $provider, $workOrder];
    }
}
