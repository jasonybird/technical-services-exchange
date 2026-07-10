<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->text('primary_objective')->nullable()->after('scope');
            $table->text('included_work')->nullable()->after('primary_objective');
            $table->text('excluded_work')->nullable()->after('included_work');
            $table->text('maximum_onsite_expectations')->nullable()->after('excluded_work');
            $table->string('expected_duration')->nullable()->after('maximum_onsite_expectations');
            $table->string('schedule_type')->nullable()->after('time_window');
            $table->boolean('remote_eligible')->default(false)->after('schedule_type');
            $table->text('required_certifications')->nullable()->after('required_tools');
            $table->text('required_safety_gear')->nullable()->after('required_certifications');
            $table->text('closeout_conditions')->nullable()->after('deliverables');
            $table->text('buyer_provided_equipment')->nullable()->after('closeout_conditions');
            $table->text('provider_provided_equipment')->nullable()->after('buyer_provided_equipment');
            $table->text('return_shipment_expectations')->nullable()->after('provider_provided_equipment');
            $table->text('parking_access_notes')->nullable()->after('return_shipment_expectations');
            $table->text('onsite_restrictions')->nullable()->after('parking_access_notes');
            $table->text('supplemental_instructions')->nullable()->after('onsite_restrictions');
            $table->string('scope_clarity_status')->default('needs_review')->after('supplemental_instructions');
            $table->json('risk_flags')->nullable()->after('scope_clarity_status');
            $table->string('primary_contact_name')->nullable()->after('vendor_onboarding');
            $table->string('primary_contact_phone')->nullable()->after('primary_contact_name');
            $table->string('primary_contact_email')->nullable()->after('primary_contact_phone');
            $table->string('backup_contact_name')->nullable()->after('primary_contact_email');
            $table->string('backup_contact_phone')->nullable()->after('backup_contact_name');
            $table->string('backup_contact_email')->nullable()->after('backup_contact_phone');
            $table->string('dispatch_contact_name')->nullable()->after('backup_contact_email');
            $table->string('dispatch_contact_phone')->nullable()->after('dispatch_contact_name');
            $table->string('dispatch_contact_email')->nullable()->after('dispatch_contact_phone');
            $table->string('technical_bridge')->nullable()->after('dispatch_contact_email');
            $table->string('escalation_contact')->nullable()->after('technical_bridge');
            $table->string('support_channel')->nullable()->after('escalation_contact');
            $table->string('support_expected_response_time')->nullable()->after('support_channel');
            $table->string('support_availability_window')->nullable()->after('support_expected_response_time');
            $table->boolean('contact_certified')->default(false)->after('support_availability_window');
            $table->foreignId('contact_certified_by_id')->nullable()->after('contact_certified')->constrained('users')->nullOnDelete();
            $table->timestamp('contact_certified_at')->nullable()->after('contact_certified_by_id');
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->json('scope_snapshot')->nullable()->after('required_evidence');
            $table->json('contact_snapshot')->nullable()->after('scope_snapshot');
            $table->string('scope_clarity_status')->default('needs_review')->after('contact_snapshot');
            $table->json('risk_flags')->nullable()->after('scope_clarity_status');
        });

        Schema::table('disputes', function (Blueprint $table) {
            $table->string('reason_code')->nullable()->after('summary');
        });

        Schema::table('dispute_votes', function (Blueprint $table) {
            $table->string('reason_code')->nullable()->after('recommendation');
        });
    }

    public function down(): void
    {
        Schema::table('dispute_votes', function (Blueprint $table) {
            $table->dropColumn('reason_code');
        });

        Schema::table('disputes', function (Blueprint $table) {
            $table->dropColumn('reason_code');
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['scope_snapshot', 'contact_snapshot', 'scope_clarity_status', 'risk_flags']);
        });

        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contact_certified_by_id');
            $table->dropColumn([
                'primary_objective',
                'included_work',
                'excluded_work',
                'maximum_onsite_expectations',
                'expected_duration',
                'schedule_type',
                'remote_eligible',
                'required_certifications',
                'required_safety_gear',
                'closeout_conditions',
                'buyer_provided_equipment',
                'provider_provided_equipment',
                'return_shipment_expectations',
                'parking_access_notes',
                'onsite_restrictions',
                'supplemental_instructions',
                'scope_clarity_status',
                'risk_flags',
                'primary_contact_name',
                'primary_contact_phone',
                'primary_contact_email',
                'backup_contact_name',
                'backup_contact_phone',
                'backup_contact_email',
                'dispatch_contact_name',
                'dispatch_contact_phone',
                'dispatch_contact_email',
                'technical_bridge',
                'escalation_contact',
                'support_channel',
                'support_expected_response_time',
                'support_availability_window',
                'contact_certified',
                'contact_certified_at',
            ]);
        });
    }
};
