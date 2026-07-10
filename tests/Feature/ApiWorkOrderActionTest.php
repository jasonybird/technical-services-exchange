<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiWorkOrderActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_api_status_transition_records_audit_location(): void
    {
        [$buyer, $provider, $workOrder] = $this->workOrderFixture();
        Sanctum::actingAs($provider, ['work-orders:read', 'work-orders:write']);

        $this->patchJson("/api/v1/work-orders/{$workOrder->id}/transition", [
            'status' => 'en_route',
            'latitude' => 36.1539816,
            'longitude' => -95.992775,
            'accuracy_meters' => 25,
        ])->assertOk()
            ->assertJsonPath('data.status', 'en_route')
            ->assertJsonPath('data.privacy.geolocation', 'Optional coordinates are stored only as work-order evidence for the submitted action.');

        $this->assertDatabaseHas('work_orders', [
            'id' => $workOrder->id,
            'status' => 'en_route',
        ]);
        $this->assertDatabaseHas('work_order_mobile_events', [
            'work_order_id' => $workOrder->id,
            'user_id' => $provider->id,
            'event_type' => 'status_transition',
            'accuracy_meters' => 25,
        ]);
    }

    public function test_mobile_api_requires_token_abilities_and_participation(): void
    {
        [, $provider, $workOrder] = $this->workOrderFixture();
        $outsider = $this->userWithRole('provider');

        Sanctum::actingAs($provider, ['work-orders:read']);
        $this->patchJson("/api/v1/work-orders/{$workOrder->id}/transition", [
            'status' => 'en_route',
        ])->assertForbidden();

        Sanctum::actingAs($outsider, ['work-orders:read', 'work-orders:write']);
        $this->getJson("/api/v1/work-orders/{$workOrder->id}")
            ->assertForbidden();
    }

    public function test_mobile_api_updates_checklist_sends_message_and_uploads_evidence(): void
    {
        Storage::fake('public');
        config()->set('provider-exchange.attachments.disk', 'public');
        config()->set('provider-exchange.attachments.root', 'test-api-assets');

        [$buyer, $provider, $workOrder] = $this->workOrderFixture([
            'deliverables_checklist' => "Arrival photo\nCable test",
            'checklist_items' => ['Arrival photo', 'Cable test'],
        ]);
        Sanctum::actingAs($provider, ['work-orders:read', 'work-orders:write', 'work-orders:upload']);

        $this->patchJson("/api/v1/work-orders/{$workOrder->id}/checklist", [
            'checklist_completed' => [
                'Arrival photo' => true,
                'Cable test' => false,
            ],
        ])->assertOk()
            ->assertJsonPath('data.checklist.0.completed', true)
            ->assertJsonPath('data.checklist.1.completed', false);

        $this->postJson("/api/v1/work-orders/{$workOrder->id}/messages", [
            'body' => 'Uploaded the arrival photo.',
        ])->assertCreated()
            ->assertJsonPath('data.body', 'Uploaded the arrival photo.');

        $this->post("/api/v1/work-orders/{$workOrder->id}/evidence", [
            'kind' => 'arrival_photo',
            'caption' => 'Arrival photo',
            'file' => UploadedFile::fake()->image('arrival.jpg'),
        ], ['Accept' => 'application/json'])->assertCreated()
            ->assertJsonPath('data.kind', 'arrival_photo')
            ->assertJsonPath('data.caption', 'Arrival photo');

        $attachment = $workOrder->attachments()->firstOrFail();
        Storage::disk('public')->assertExists($attachment->path);
        $this->assertDatabaseHas('work_order_mobile_events', [
            'work_order_id' => $workOrder->id,
            'event_type' => 'evidence_uploaded',
        ]);
    }

    public function test_mobile_api_records_contact_failure_running_late_schedule_update_and_dispute(): void
    {
        [$buyer, $provider, $workOrder] = $this->workOrderFixture();
        Sanctum::actingAs($provider, ['work-orders:read', 'work-orders:write', 'disputes:write']);

        $this->postJson("/api/v1/work-orders/{$workOrder->id}/contact-events", [
            'event_type' => 'support_unavailable',
            'attempted_channel' => 'Phone',
            'result' => 'No answer',
        ])->assertCreated()
            ->assertJsonPath('data.event_type', 'support_unavailable');

        $this->postJson("/api/v1/work-orders/{$workOrder->id}/running-late", [
            'estimated_arrival_at' => now()->addMinutes(30)->toIso8601String(),
            'reason' => 'Traffic delay',
        ])->assertCreated()
            ->assertJsonPath('data.event_type', 'running_late');

        $this->postJson("/api/v1/work-orders/{$workOrder->id}/schedule-updates", [
            'requested_schedule_at' => now()->addDay()->toIso8601String(),
            'summary' => 'Move to tomorrow morning',
            'details' => 'Site contact asked to reschedule.',
        ])->assertCreated()
            ->assertJsonPath('data.reason_code', 'schedule_change');

        $this->postJson("/api/v1/work-orders/{$workOrder->id}/disputes", [
            'summary' => 'Support unavailable',
            'reason_code' => 'support_unavailable',
            'claim' => 'The certified support line did not answer.',
        ])->assertCreated()
            ->assertJsonPath('data.reason_code', 'support_unavailable');

        $this->assertDatabaseHas('work_order_contact_events', [
            'work_order_id' => $workOrder->id,
            'event_type' => 'support_unavailable',
        ]);
        $this->assertDatabaseHas('work_order_change_requests', [
            'work_order_id' => $workOrder->id,
            'reason_code' => 'schedule_change',
        ]);
        $this->assertDatabaseHas('disputes', [
            'work_order_id' => $workOrder->id,
            'reason_code' => 'support_unavailable',
        ]);
        $this->assertDatabaseHas('work_orders', [
            'id' => $workOrder->id,
            'status' => 'disputed',
        ]);
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function workOrderFixture(array $overrides = []): array
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

        $workOrder = WorkOrder::create($overrides + [
            'job_post_id' => $job->id,
            'buyer_id' => $buyer->id,
            'provider_id' => $provider->id,
            'status' => 'assigned',
        ]);

        return [$buyer, $provider, $workOrder];
    }
}
