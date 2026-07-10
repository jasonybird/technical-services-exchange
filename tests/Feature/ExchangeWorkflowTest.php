<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\ProviderProfile;
use App\Models\SocialPost;
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
            'summary' => 'Add second terminal',
            'details' => 'Buyer requested another POS terminal onsite.',
        ])->assertRedirect("/work-orders/{$workOrder->id}");

        $this->assertDatabaseHas('work_orders', ['id' => $workOrder->id]);
        $this->assertSame('Add second terminal', $workOrder->fresh()->changeRequests()[0]['summary']);

        $this->actingAs($buyer)->get("/work-orders/{$workOrder->id}/print")
            ->assertOk()
            ->assertSee('Arrival photo')
            ->assertSee('Print');
    }

    public function test_external_profile_snapshot_can_be_saved(): void
    {
        $provider = $this->userWithRole('provider');
        $provider->providerProfile()->create(['business_name' => 'Provider LLC']);

        $this->actingAs($provider)->post('/provider-profile/imports', [
            'platform' => 'Field Nation',
            'external_id' => '172-630',
            'rating' => 4.9,
            'review_count' => 500,
            'completed_jobs' => 1200,
        ])->assertRedirect('/provider-profile');

        $this->assertDatabaseHas('external_profile_imports', [
            'platform' => 'Field Nation',
            'external_id' => '172-630',
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
