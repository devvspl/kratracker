<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->boolean('is_cloned')->default(false)->after('remark');
            $table->unsignedBigInteger('cloned_from_id')->nullable()->after('is_cloned');
        });
    }

    public function down(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->dropColumn(['is_cloned', 'cloned_from_id']);
        });
    }
};
