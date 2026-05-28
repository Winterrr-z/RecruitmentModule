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
        Schema::create('recruitment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpp_id')->constrained('mpps')->onDelete('cascade');
            $table->string('jabatan', 100);
            $table->string('departemen', 100);
            $table->integer('estimasi_gaji_min')->nullable();
            $table->integer('estimasi_gaji_max')->nullable();
            $table->date('expected_join_date')->nullable();
            $table->text('deskripsi_pekerjaan');
            $table->text('spesifikasi_kebutuhan')->nullable();
            $table->enum('tipe_kerja', ['full-time', 'contract']);
            $table->enum('lokasi', ['remote', 'on-site']);
            $table->date('application_deadline');
            $table->boolean('tampilkan_gaji')->default(false);
            $table->string('status', 30)->default('Draft');
            $table->integer('kuota');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_requests');
    }
};
