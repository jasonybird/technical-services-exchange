<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->actingAs($provider)->patch("/work-orders/{$workOrder->id}/transition", [
            'status' => 'completed',
            'completion_notes' => 'Completed with photos.',
        ])->assertRedirect("/work-orders/{$workOrder->id}");

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
