<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_tag_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('declared_level')->nullable();
            $table->unsignedTinyInteger('confirmed_level')->nullable();
            $table->string('level_verdict')->default('not_observed');
            $table->json('confirmed_term_ids')->nullable();
            $table->json('disputed_term_ids')->nullable();
            $table->json('suggested_tags')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('work_order_id');
            $table->index(['provider_profile_id', 'level_verdict']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_tag_verifications');
    }
};
