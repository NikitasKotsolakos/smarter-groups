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
        Schema::table('groups_students', function (Blueprint $table) {
            $table->string('assignment_method')->nullable()->after('student_id');
            $table->timestamp('assigned_at')->nullable()->after('assignment_method');
            $table->foreignId('assigned_by')->nullable()->after('assigned_at')
                  ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups_students', function (Blueprint $table) {
            $table->dropForeign(['assigned_by']);
            $table->dropColumn(['assignment_method', 'assigned_at', 'assigned_by']);
        });
    }
};
