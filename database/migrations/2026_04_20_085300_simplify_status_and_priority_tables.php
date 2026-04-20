<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_statuses', function (Blueprint $table) {
            $table->dropColumn(['color_class', 'is_active']);
        });

        Schema::table('priorities', function (Blueprint $table) {
            $table->dropColumn(['color_class', 'level', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('task_statuses', function (Blueprint $table) {
            $table->string('color_class')->default('slate');
            $table->boolean('is_active')->default(true);
        });

        Schema::table('priorities', function (Blueprint $table) {
            $table->string('color_class')->default('slate');
            $table->integer('level')->default(0);
            $table->boolean('is_active')->default(true);
        });
    }
};
