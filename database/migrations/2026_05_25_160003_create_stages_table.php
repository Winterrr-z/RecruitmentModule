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
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('butuh_scorecard')->default(false);
            $table->boolean('butuh_jadwal')->default(false);
            $table->integer('urutan');
            $table->timestamps();
        });

        DB::table('stages')->insert([
            [
                'id' => 1,
                'nama' => 'Applied',
                'deskripsi' => 'Default applied stage',
                'butuh_scorecard' => false,
                'butuh_jadwal' => false,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nama' => 'Final',
                'deskripsi' => 'Default final stage',
                'butuh_scorecard' => false,
                'butuh_jadwal' => false,
                'urutan' => 999,
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
