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
        Schema::create('provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name');
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->string('service_area')->nullable();
            $table->text('skills')->nullable();
            $table->text('tools')->nullable();
            $table->text('certifications')->nullable();
            $table->string('insurance_status')->nullable();
            $table->text('rate_card')->nullable();
            $table->text('travel_policy')->nullable();
            $table->text('availability_notes')->nullable();
            $table->string('website_url')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('public_contact')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_profiles');
    }
};
