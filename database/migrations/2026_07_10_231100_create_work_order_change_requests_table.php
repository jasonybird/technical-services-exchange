<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('responder_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason_code')->default('scope_expansion');
            $table->string('summary');
            $table->text('details')->nullable();
            $table->text('scope_impact')->nullable();
            $table->text('schedule_impact')->nullable();
            $table->text('terms_impact')->nullable();
            $table->string('status')->default('open');
            $table->text('resolution_notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['work_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_change_requests');
    }
};
