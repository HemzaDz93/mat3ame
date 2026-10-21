<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainees')->cascadeOnDelete();
            $table->date('day');
            $table->timestamp('served_at')->nullable();
            $table->foreignId('served_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['trainee_id', 'day']);
            $table->index('day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};
