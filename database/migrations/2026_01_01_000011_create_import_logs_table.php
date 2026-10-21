<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('sheet')->nullable();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('accepted')->default(0);
            $table->unsignedInteger('updated')->default(0);
            $table->unsignedInteger('rejected')->default(0);
            $table->unsignedInteger('skipped')->default(0);
            $table->json('report')->nullable();
            $table->enum('status', ['success', 'failed', 'partial'])->default('success');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
