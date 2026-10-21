<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_forecasts', function (Blueprint $table) {
            $table->id();
            $table->date('day')->unique();
            $table->unsignedInteger('expected_count')->default(0);
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_forecasts');
    }
};
