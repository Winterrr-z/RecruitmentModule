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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->nullable()->constrained('vacancies')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('name', 100);
            $table->string('email', 100);
            $table->string('phone', 20);
            $table->string('cv_path', 200)->nullable();
            $table->string('portofolio_path', 200)->nullable();
            $table->foreignId('current_stage_id')->default(1)->constrained('stages');
            $table->string('status', 50)->default('Applied');
            $table->enum('source', ['public', 'manual'])->default('public');
            $table->string('offering_token', 100)->nullable();
            $table->timestamp('offering_token_expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
