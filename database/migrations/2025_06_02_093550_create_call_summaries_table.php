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
        Schema::create('call_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('physio_id'); // Foreign key to users or physios
            $table->text('transcript')->nullable();
            $table->text('summary')->nullable();
            $table->string('recording_url')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->decimal('cost', 8, 2)->nullable();
            $table->string('assistant_name')->nullable();
            $table->timestamps();

            $table->foreign('physio_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_summaries');
    }
};
