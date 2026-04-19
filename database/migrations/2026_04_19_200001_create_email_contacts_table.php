<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('role')->nullable();           // e.g. Client, Stakeholder, HR
            $table->text('notes')->nullable();
            $table->boolean('notify_on_complete')->default(true);   // task completed
            $table->boolean('notify_on_status_change')->default(false); // any status update
            $table->boolean('notify_on_daily_report')->default(false);
            $table->boolean('notify_on_weekly_report')->default(false);
            $table->boolean('notify_on_monthly_report')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_contacts');
    }
};
