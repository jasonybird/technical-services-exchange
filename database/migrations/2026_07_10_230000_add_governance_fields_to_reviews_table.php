<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedTinyInteger('preparedness_rating')->nullable()->after('communication_rating');
            $table->unsignedTinyInteger('closeout_quality_rating')->nullable()->after('timeliness_rating');
            $table->unsignedTinyInteger('professionalism_rating')->nullable()->after('closeout_quality_rating');
            $table->unsignedTinyInteger('contact_availability_rating')->nullable()->after('payment_reliability_rating');
            $table->unsignedTinyInteger('schedule_reasonableness_rating')->nullable()->after('contact_availability_rating');
            $table->unsignedTinyInteger('support_responsiveness_rating')->nullable()->after('schedule_reasonableness_rating');
            $table->unsignedTinyInteger('closeout_fairness_rating')->nullable()->after('support_responsiveness_rating');
            $table->text('response_body')->nullable()->after('body');
            $table->timestamp('response_at')->nullable()->after('response_body');
            $table->timestamp('reported_at')->nullable()->after('response_at');
            $table->foreignId('reported_by_id')->nullable()->after('reported_at')->constrained('users')->nullOnDelete();
            $table->text('report_reason')->nullable()->after('reported_by_id');
            $table->string('moderation_status')->default('published')->after('report_reason');
            $table->foreignId('moderated_by_id')->nullable()->after('moderation_status')->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable()->after('moderated_by_id');
            $table->text('moderation_notes')->nullable()->after('moderated_at');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reported_by_id');
            $table->dropConstrainedForeignId('moderated_by_id');
            $table->dropColumn([
                'preparedness_rating',
                'closeout_quality_rating',
                'professionalism_rating',
                'contact_availability_rating',
                'schedule_reasonableness_rating',
                'support_responsiveness_rating',
                'closeout_fairness_rating',
                'response_body',
                'response_at',
                'reported_at',
                'report_reason',
                'moderation_status',
                'moderated_at',
                'moderation_notes',
            ]);
        });
    }
};
