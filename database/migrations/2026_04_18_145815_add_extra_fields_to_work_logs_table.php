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
            $table->decimal('total_duration', 5, 2)->default(0)->after('time_spent_hours');
            $table->decimal('actual_duration', 5, 2)->default(0)->after('total_duration');
            $table->string('test_status')->nullable()->after('actual_duration');
            $table->text('remark')->nullable()->after('test_status');
        });
    }

    public function down(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->dropColumn(['total_duration', 'actual_duration', 'test_status', 'remark']);
        });
    }
};
