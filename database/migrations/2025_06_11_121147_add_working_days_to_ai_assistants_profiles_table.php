<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ai_assistants_profiles', function (Blueprint $table) {
            $table->string('working_days')->after('working_hours_end');
        });
    }

    public function down()
    {
        Schema::table('ai_assistants_profiles', function (Blueprint $table) {
            $table->dropColumn('working_days');
        });
    }
};
