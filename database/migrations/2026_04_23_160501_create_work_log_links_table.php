<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_log_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_log_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('url');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_log_links');
    }
};