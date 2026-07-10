<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->boolean('in_app_enabled')->default(true)->after('dispute_updates');
            $table->json('event_preferences')->nullable()->after('push_enabled');
            $table->string('digest_frequency')->default('immediate')->after('event_preferences');
            $table->time('quiet_hours_start')->nullable()->after('digest_frequency');
            $table->time('quiet_hours_end')->nullable()->after('quiet_hours_start');
        });
    }

    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->dropColumn([
                'in_app_enabled',
                'event_preferences',
                'digest_frequency',
                'quiet_hours_start',
                'quiet_hours_end',
            ]);
        });
    }
};
