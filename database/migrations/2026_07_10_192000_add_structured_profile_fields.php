<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->json('services')->nullable()->after('skills');
            $table->json('tool_inventory')->nullable()->after('tools');
            $table->json('certification_records')->nullable()->after('certifications');
            $table->json('profile_visibility')->nullable()->after('public_contact');
            $table->text('private_notes')->nullable()->after('profile_visibility');
        });

        Schema::table('buyer_profiles', function (Blueprint $table) {
            $table->json('hiring_policies')->nullable()->after('hiring_regions');
            $table->json('locations')->nullable()->after('hiring_policies');
            $table->json('profile_visibility')->nullable()->after('public_contact');
            $table->text('private_notes')->nullable()->after('profile_visibility');
        });
    }

    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'services',
                'tool_inventory',
                'certification_records',
                'profile_visibility',
                'private_notes',
            ]);
        });

        Schema::table('buyer_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'hiring_policies',
                'locations',
                'profile_visibility',
                'private_notes',
            ]);
        });
    }
};
