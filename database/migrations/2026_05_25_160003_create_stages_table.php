<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('needs_scorecard')->default(false);
            $table->boolean('needs_schedule')->default(false);
            $table->integer('sequence');
            
            // Predefined Configuration Templates
            $table->text('scorecard_criteria')->nullable(); // JSON Array
            $table->string('interview_type', 50)->nullable(); // online, offline, hybrid
            $table->string('default_location', 200)->nullable();
            $table->string('default_virtual_link', 200)->nullable();

            $table->timestamps();
        });

        DB::table('stages')->insert([
            [
                'id' => 1,
                'name' => 'Applied',
                'description' => 'Default applied stage',
                'needs_scorecard' => false,
                'needs_schedule' => false,
                'sequence' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Final',
                'description' => 'Default final stage',
                'needs_scorecard' => false,
                'needs_schedule' => false,
                'sequence' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
