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
        Schema::create('work_log_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_log_id')->constrained('work_logs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('feedback_type', ['self', 'manager'])->default('self');
            $table->text('comment')->nullable();
            $table->integer('rating')->default(3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_log_feedbacks');
    }
};
