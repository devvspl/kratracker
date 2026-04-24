<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_configs', function (Blueprint $table) {
            $table->dropForeign(['employee_user_id']);
            $table->dropColumn('employee_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('report_configs', function (Blueprint $table) {
            $table->foreignId('employee_user_id')->nullable()->constrained('users')->onDelete('cascade');
        });
    }
};
