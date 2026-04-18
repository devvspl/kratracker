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
        Schema::create('work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sub_kra_id')->constrained('sub_kras')->onDelete('restrict');
            $table->foreignId('application_id')->nullable()->constrained('applications')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('log_date');
            $table->foreignId('priority_id')->constrained('priorities')->onDelete('restrict');
            $table->foreignId('status_id')->constrained('task_statuses')->onDelete('restrict');
            $table->decimal('achievement_value', 10, 2)->default(0);
            $table->decimal('target_value_snapshot', 10, 2)->default(0);
            $table->decimal('score_calculated', 5, 2)->default(0);
            $table->string('logic_applied')->nullable();
            $table->decimal('time_spent_hours', 5, 2)->default(0);
            $table->json('attachments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_logs');
    }
};
