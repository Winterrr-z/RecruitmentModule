<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Stage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Menyiapkan 3 HR
        User::updateOrCreate(
            ['email' => 'hr1@company.com'],
            [
                'name' => 'HR Manager',
                'password' => Hash::make('HrPassword'),
                'role' => 'hr',
            ]
        );
        
        User::updateOrCreate(
            ['email' => 'hr2@company.com'],
            [
                'name' => 'HR Staff 1',
                'password' => Hash::make('HrPassword'),
                'role' => 'hr',
            ]
        );

        User::updateOrCreate(
            ['email' => 'hr3@company.com'],
            [
                'name' => 'HR Staff 2',
                'password' => Hash::make('HrPassword'),
                'role' => 'hr',
            ]
        );

        // 2. Menyiapkan 2 Stage Default (Applied dan Final)
        Stage::updateOrCreate(
            ['id' => 1], // ID 1 wajib untuk Applied stage
            [
                'name' => 'Applied',
                'description' => 'Kandidat baru saja melamar lowongan',
                'needs_scorecard' => false,
                'needs_schedule' => false,
                'sequence' => 1,
                'is_first_stage' => true,
                'is_final_stage' => false,
            ]
        );

        Stage::updateOrCreate(
            ['id' => 2], // ID 2 digunakan untuk Final stage
            [
                'name' => 'Final',
                'description' => 'Tahap keputusan penawaran (offering)',
                'needs_scorecard' => false,
                'needs_schedule' => false,
                'sequence' => 999,
                'is_first_stage' => false,
                'is_final_stage' => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Opsional: Hapus user HR dan stage bawaan jika migrasi ini di-rollback
        // Namun, umumnya data ini dibiarkan (atau Anda bisa menghapusnya manual)
        User::whereIn('email', ['hr1@company.com', 'hr2@company.com', 'hr3@company.com'])->delete();
        Stage::whereIn('id', [1, 2])->delete();
    }
};
