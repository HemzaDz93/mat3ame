<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainees', function (Blueprint $table) {
            $table->id();
            $table->string('reg_no')->nullable();
            $table->string('electronic_reg_no')->nullable();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('last_name_fr')->nullable();
            $table->string('first_name_fr')->nullable();
            $table->enum('gender', ['M', 'F'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('birth_wilaya')->nullable();
            $table->string('residence_wilaya')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_last_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('nin')->nullable();
            $table->string('specialty')->nullable();
            $table->string('diploma')->nullable();
            $table->string('level')->nullable();
            $table->string('training_mode')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('eligible')->default(false);
            $table->boolean('file_accepted')->default(false);
            $table->boolean('deliberation_accepted')->default(false);
            $table->boolean('account_cancelled')->default(false);
            $table->string('special_needs')->nullable();
            $table->string('photo')->nullable();
            $table->string('source_photo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique('electronic_reg_no');
            $table->index('reg_no');
            $table->index('nin');
            $table->index('specialty');
            $table->index('last_name');
            $table->index('phone');
            $table->index('start_date');
            $table->index('created_at');
            $table->index(['deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainees');
    }
};
