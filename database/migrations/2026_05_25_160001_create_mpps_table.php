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
        Schema::create('mpps', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name', 200);
            $table->string('department', 100);
            $table->string('job_title', 100);
            $table->integer('quota');
            $table->integer('estimated_salary_min')->nullable();
            $table->integer('estimated_salary_max')->nullable();
            $table->integer('sla_days');
            $table->date('absolute_target_date');
            $table->string('status', 20)->default('Draft');
            $table->text('note')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpps');
    }
};
