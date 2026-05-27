<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('stages')->updateOrInsert(
            ['id' => 1],
            [
                'nama' => 'Applied',
                'deskripsi' => 'Default applied stage',
                'butuh_scorecard' => false,
                'butuh_jadwal' => false,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('stages')->updateOrInsert(
            ['id' => 2],
            [
                'nama' => 'Final',
                'deskripsi' => 'Default final stage',
                'butuh_scorecard' => false,
                'butuh_jadwal' => false,
                'urutan' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'hr@company.com'],
            [
                'name' => 'HR',
                'password' => Hash::make('Hr12345'),
                'role' => 'hr',
                'created_at' => now(),
            ]
        );
    }
}
