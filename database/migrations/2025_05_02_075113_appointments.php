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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('full_name', 100);
            $table->string('phone_number', 20);
            $table->string('service_type', 100);
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('appointment_slot')->nullable(); // Added appointment slot field
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no-show'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
