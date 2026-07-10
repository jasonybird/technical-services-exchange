<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('external_profile_imports', function (Blueprint $table) {
            $table->json('work_categories')->nullable()->after('completed_jobs');
            $table->json('endorsements')->nullable()->after('work_categories');
            $table->json('operational_metrics')->nullable()->after('endorsements');
            $table->json('selected_reviews')->nullable()->after('review_snapshots');
            $table->string('visibility')->default('private')->after('status');
            $table->string('verification_status')->default('unverified')->after('visibility');
            $table->foreignId('verified_by_id')->nullable()->after('verification_status')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('external_profile_imports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by_id');
            $table->dropColumn([
                'work_categories',
                'endorsements',
                'operational_metrics',
                'selected_reviews',
                'visibility',
                'verification_status',
                'verified_at',
            ]);
        });
    }
};
