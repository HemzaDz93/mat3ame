<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('emp_no')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('rank')->nullable();
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('last_name');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
