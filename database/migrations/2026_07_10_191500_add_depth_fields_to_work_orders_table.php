<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('deliverables_checklist');
            $table->string('appointment_window')->nullable()->after('scheduled_at');
            $table->json('checklist_items')->nullable()->after('appointment_window');
            $table->json('checklist_completed')->nullable()->after('checklist_items');
            $table->text('required_evidence')->nullable()->after('checklist_completed');
            $table->json('evidence_rules')->nullable()->after('required_evidence');
            $table->json('change_requests')->nullable()->after('evidence_rules');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn([
                'scheduled_at',
                'appointment_window',
                'checklist_items',
                'checklist_completed',
                'required_evidence',
                'evidence_rules',
                'change_requests',
            ]);
        });
    }
};
