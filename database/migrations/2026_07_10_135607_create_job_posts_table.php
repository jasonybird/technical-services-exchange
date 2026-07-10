<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('status')->default('open');
            $table->string('service_category')->nullable();
            $table->string('location');
            $table->timestamp('starts_at')->nullable();
            $table->string('time_window')->nullable();
            $table->text('scope');
            $table->text('required_skills')->nullable();
            $table->text('required_tools')->nullable();
            $table->text('deliverables')->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('vendor_onboarding')->nullable();
            $table->string('visibility')->default('public');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
