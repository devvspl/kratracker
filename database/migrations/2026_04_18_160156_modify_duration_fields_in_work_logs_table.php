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
        Schema::table('work_logs', function (Blueprint $table) {
            $table->dropColumn('time_spent_hours');
            $table->decimal('duration_difference', 8, 2)->nullable()->after('actual_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->decimal('time_spent_hours', 8, 2)->nullable();
            $table->dropColumn('duration_difference');
        });
    }
};
