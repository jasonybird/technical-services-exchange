<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxonomy_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('taxonomy_terms')->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'slug']);
            $table->index(['type', 'is_active', 'sort_order']);
        });

        Schema::create('provider_profile_taxonomy_term', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('taxonomy_term_id')->constrained()->cascadeOnDelete();
            $table->string('evidence_source')->default('self_declared');
            $table->timestamps();

            $table->unique(['provider_profile_id', 'taxonomy_term_id']);
        });

        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_technician_level')->default(1)->after('service_area');
        });

        Schema::table('job_posts', function (Blueprint $table) {
            $table->foreignId('work_category_id')->nullable()->after('service_category')->constrained('taxonomy_terms')->nullOnDelete();
            $table->foreignId('work_specialty_id')->nullable()->after('work_category_id')->constrained('taxonomy_terms')->nullOnDelete();
            $table->unsignedTinyInteger('required_technician_level')->default(1)->after('work_specialty_id');
            $table->string('work_mode')->default('onsite')->after('required_technician_level');
            $table->string('pay_type')->nullable()->after('work_mode');
            $table->string('posted_terms_summary')->nullable()->after('pay_type');
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('work_category_id');
            $table->dropConstrainedForeignId('work_specialty_id');
            $table->dropColumn(['required_technician_level', 'work_mode', 'pay_type', 'posted_terms_summary']);
        });

        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn('max_technician_level');
        });

        Schema::dropIfExists('provider_profile_taxonomy_term');
        Schema::dropIfExists('taxonomy_terms');
    }
};
