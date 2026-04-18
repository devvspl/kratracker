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
        Schema::create('period_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_kra_id')->constrained('sub_kras')->onDelete('cascade');
            $table->decimal('target_value', 10, 2);
            $table->enum('period_type', ['Monthly', 'Quarterly', 'Annually']);
            $table->year('period_year');
            $table->integer('period_month_or_quarter')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('period_targets');
    }
};
