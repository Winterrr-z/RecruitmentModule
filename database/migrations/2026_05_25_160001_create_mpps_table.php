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
            $table->string('nama_plan', 200);
            $table->string('departemen', 100);
            $table->string('jabatan', 100);
            $table->integer('jumlah_kebutuhan');
            $table->integer('estimasi_gaji_min')->nullable();
            $table->integer('estimasi_gaji_max')->nullable();
            $table->string('syarat_pendidikan', 50);
            $table->string('syarat_pengalaman', 50);
            $table->json('keahlian');
            $table->integer('sla_bulan');
            $table->date('target_waktu_absolut');
            $table->string('status', 20)->default('draft');
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
