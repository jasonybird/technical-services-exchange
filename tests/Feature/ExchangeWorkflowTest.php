<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\SocialPost;
use App\Models\User;
use App\Models\WorkOrder;
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
