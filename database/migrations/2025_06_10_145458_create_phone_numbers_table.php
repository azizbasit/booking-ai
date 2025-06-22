<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone_number');
            $table->string('twilio_sid');
            $table->string('vapi_assistant_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Add unique constraints
            $table->unique('phone_number');
            $table->unique('twilio_sid');
            $table->unique('vapi_assistant_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('phone_numbers');
    }
};