<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->index(['visibility', 'status', 'created_at'], 'idx_job_posts_visibility_status_created');
            $table->index(['status', 'work_category_id', 'required_technician_level'], 'idx_job_posts_board_category_level');
            $table->index(['scope_clarity_status', 'contact_certified', 'remote_eligible'], 'idx_job_posts_safety_filters');
            $table->index(['buyer_id', 'status', 'created_at'], 'idx_job_posts_buyer_status_created');
            $table->index('starts_at', 'idx_job_posts_starts_at');
        });

        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->index(['max_technician_level', 'public_contact'], 'idx_provider_profiles_level_public');
            $table->index('service_area', 'idx_provider_profiles_service_area');
            $table->index('insurance_status', 'idx_provider_profiles_insurance');
        });

        Schema::table('buyer_profiles', function (Blueprint $table) {
            $table->index('public_contact', 'idx_buyer_profiles_public_contact');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->index(['job_post_id', 'status', 'created_at'], 'idx_quotes_job_status_created');
            $table->index(['provider_id', 'status', 'created_at'], 'idx_quotes_provider_status_created');
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->index(['buyer_id', 'status', 'created_at'], 'idx_work_orders_buyer_status_created');
            $table->index(['provider_id', 'status', 'created_at'], 'idx_work_orders_provider_status_created');
            $table->index(['job_post_id', 'status'], 'idx_work_orders_job_status');
            $table->index('scheduled_at', 'idx_work_orders_scheduled_at');
        });

        Schema::table('work_order_messages', function (Blueprint $table) {
            $table->index(['work_order_id', 'created_at'], 'idx_work_order_messages_order_created');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['moderation_status', 'reported_at'], 'idx_reviews_moderation_reported');
            $table->index(['reviewee_id', 'created_at'], 'idx_reviews_reviewee_created');
            $table->index(['work_order_id', 'review_type'], 'idx_reviews_order_type');
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->index(['rateable_type', 'rateable_id', 'category', 'stars'], 'idx_ratings_target_category_stars');
        });

        Schema::table('disputes', function (Blueprint $table) {
            $table->index(['status', 'reason_code', 'created_at'], 'idx_disputes_status_reason_created');
            $table->index(['work_order_id', 'status'], 'idx_disputes_order_status');
        });

        Schema::table('attachments', function (Blueprint $table) {
            $table->index(['user_id', 'kind', 'created_at'], 'idx_attachments_user_kind_created');
            $table->index(['attachable_type', 'attachable_id', 'kind'], 'idx_attachments_target_kind');
        });

        Schema::table('external_profile_imports', function (Blueprint $table) {
            $table->index(['verification_status', 'created_at'], 'idx_external_imports_verification_created');
            $table->index(['provider_profile_id', 'visibility'], 'idx_external_imports_profile_visibility');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id', 'read_at', 'created_at'], 'idx_notifications_inbox');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_inbox');
        });

        Schema::table('external_profile_imports', function (Blueprint $table) {
            $table->dropIndex('idx_external_imports_verification_created');
            $table->dropIndex('idx_external_imports_profile_visibility');
        });

        Schema::table('attachments', function (Blueprint $table) {
            $table->dropIndex('idx_attachments_user_kind_created');
            $table->dropIndex('idx_attachments_target_kind');
        });

        Schema::table('disputes', function (Blueprint $table) {
            $table->dropIndex('idx_disputes_status_reason_created');
            $table->dropIndex('idx_disputes_order_status');
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->dropIndex('idx_ratings_target_category_stars');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('idx_reviews_moderation_reported');
            $table->dropIndex('idx_reviews_reviewee_created');
            $table->dropIndex('idx_reviews_order_type');
        });

        Schema::table('work_order_messages', function (Blueprint $table) {
            $table->dropIndex('idx_work_order_messages_order_created');
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex('idx_work_orders_buyer_status_created');
            $table->dropIndex('idx_work_orders_provider_status_created');
            $table->dropIndex('idx_work_orders_job_status');
            $table->dropIndex('idx_work_orders_scheduled_at');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex('idx_quotes_job_status_created');
            $table->dropIndex('idx_quotes_provider_status_created');
        });

        Schema::table('buyer_profiles', function (Blueprint $table) {
            $table->dropIndex('idx_buyer_profiles_public_contact');
        });

        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropIndex('idx_provider_profiles_level_public');
            $table->dropIndex('idx_provider_profiles_service_area');
            $table->dropIndex('idx_provider_profiles_insurance');
        });

        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropIndex('idx_job_posts_visibility_status_created');
            $table->dropIndex('idx_job_posts_board_category_level');
            $table->dropIndex('idx_job_posts_safety_filters');
            $table->dropIndex('idx_job_posts_buyer_status_created');
            $table->dropIndex('idx_job_posts_starts_at');
        });
    }
};
