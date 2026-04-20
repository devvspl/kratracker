<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['kras', 'logics', 'task_statuses', 'priorities', 'applications', 'application_modules'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('user_id')->nullable()->after('id')
                  ->constrained('users')->onDelete('cascade');
            });
        }

        // sub_kras inherits user scope via kra_id, no direct user_id needed
    }

    public function down(): void
    {
        $tables = ['kras', 'logics', 'task_statuses', 'priorities', 'applications', 'application_modules'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropForeign([$table === 'application_modules' ? 'application_modules_user_id_foreign' : "{$table}_user_id_foreign"]);
                $t->dropColumn('user_id');
            });
        }
    }
};
