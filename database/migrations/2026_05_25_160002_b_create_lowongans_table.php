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
        Schema::create('lowongans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_request_id')->constrained('recruitment_requests')->onDelete('cascade');
            $table->integer('quota');
            $table->string('job_title', 100);
            $table->string('department', 100);
            $table->enum('employment_type', ['full-time', 'contract']);
            $table->enum('location', ['remote', 'on-site']);
            $table->date('application_deadline');
            $table->boolean('show_salary')->default(false);
            $table->integer('estimated_salary_min')->nullable();
            $table->integer('estimated_salary_max')->nullable();
            $table->text('job_description');
            $table->text('job_requirements')->nullable();
            $table->string('status', 30)->default('Published');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lowongans');
    }
};
