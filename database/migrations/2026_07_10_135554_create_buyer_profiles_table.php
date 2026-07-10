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
        Schema::create('buyer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('headline')->nullable();
            $table->text('description')->nullable();
            $table->text('service_categories')->nullable();
            $table->text('hiring_regions')->nullable();
            $table->text('vendor_onboarding')->nullable();
            $table->text('payment_terms')->nullable();
            $table->string('website_url')->nullable();
            $table->string('contact_email')->nullable();
            $table->boolean('public_contact')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_profiles');
    }
};
