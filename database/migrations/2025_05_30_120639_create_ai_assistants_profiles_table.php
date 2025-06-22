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
        Schema::create('ai_assistants_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('specialization')->nullable();
            $table->integer('experience')->nullable();
            $table->string('clinic_name')->nullable();
            $table->string('email')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('working_hours_start')->nullable();
            $table->string('working_hours_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_assistants_profiles');
    }
};
