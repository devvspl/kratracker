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
        Schema::create('sub_kras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kra_id')->constrained('kras')->onDelete('cascade');
            $table->string('name');
            $table->decimal('weightage', 5, 2);
            $table->string('unit')->default('%'); // %/Day/Count
            $table->string('measure_type')->nullable();
            $table->foreignId('logic_id')->constrained('logics')->onDelete('restrict');
            $table->enum('review_period', ['Monthly', 'Quarterly', 'Annually'])->default('Monthly');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_kras');
    }
};
