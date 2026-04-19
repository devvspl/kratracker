<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->foreignId('module_id')->nullable()->after('application_id')
                  ->constrained('application_modules')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropColumn('module_id');
        });
    }
};
