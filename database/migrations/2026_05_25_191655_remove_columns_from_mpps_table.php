<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mpps', function (Blueprint $table) {
            $table->dropColumn(['syarat_pendidikan', 'syarat_pengalaman', 'keahlian']);
        });
    }

    public function down(): void
    {
        Schema::table('mpps', function (Blueprint $table) {
            $table->string('syarat_pendidikan', 50)->nullable();
            $table->string('syarat_pengalaman', 50)->nullable();
            $table->json('keahlian')->nullable();
        });
    }
};