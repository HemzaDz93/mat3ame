<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainees', function (Blueprint $table) {
            $table->dropIndex('trainees_reg_no_index');
            $table->unique('reg_no');
        });
    }

    public function down(): void
    {
        Schema::table('trainees', function (Blueprint $table) {
            $table->dropUnique('trainees_reg_no_unique');
            $table->index('reg_no');
        });
    }
};
