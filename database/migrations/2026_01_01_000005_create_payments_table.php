<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainee_id')->constrained('trainees')->cascadeOnDelete();
            $table->enum('plan', ['day', 'month', 'quarter']);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['trainee_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
