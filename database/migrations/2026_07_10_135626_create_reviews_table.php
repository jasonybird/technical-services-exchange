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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('communication_rating')->nullable();
            $table->unsignedTinyInteger('scope_accuracy_rating')->nullable();
            $table->unsignedTinyInteger('payment_reliability_rating')->nullable();
            $table->unsignedTinyInteger('workmanship_rating')->nullable();
            $table->unsignedTinyInteger('timeliness_rating')->nullable();
            $table->text('body')->nullable();
            $table->string('review_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
