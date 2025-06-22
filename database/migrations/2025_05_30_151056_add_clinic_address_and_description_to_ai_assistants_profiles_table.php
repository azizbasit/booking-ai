<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClinicAddressAndDescriptionToAiAssistantsProfilesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_assistants_profiles', function (Blueprint $table) {
            $table->string('clinic_address')->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_assistants_profiles', function (Blueprint $table) {
            $table->dropColumn(['clinic_address', 'description']);
        });
    }
};
