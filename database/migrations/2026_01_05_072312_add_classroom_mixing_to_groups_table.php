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
        Schema::table('groups', function (Blueprint $table) {
            $table->integer('max_students_from_one_classroom')
                  ->nullable()
                  ->after('maximumParticipants')
                  ->comment('Maximum students from same classroom. NULL = no limit (uses maximumParticipants)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('max_students_from_one_classroom');
        });
    }
};
