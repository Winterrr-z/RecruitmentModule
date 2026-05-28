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
            $table->integer('kuota');
            $table->string('jabatan', 100);
            $table->string('departemen', 100);
            $table->enum('tipe_kerja', ['full-time', 'contract']);
            $table->enum('lokasi', ['remote', 'on-site']);
            $table->date('application_deadline');
            $table->boolean('tampilkan_gaji')->default(false);
            $table->integer('estimasi_gaji_min')->nullable();
            $table->integer('estimasi_gaji_max')->nullable();
            $table->text('deskripsi_pekerjaan');
            $table->text('spesifikasi_kebutuhan')->nullable();
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
