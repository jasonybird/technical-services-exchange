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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('rateable');
            $table->string('category')->default('overall');
            $table->unsignedTinyInteger('stars')->nullable();
            $table->boolean('thumbs_up')->nullable();
            $table->text('body')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'rateable_type', 'rateable_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
