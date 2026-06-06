<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Mpp;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mpp>
 */
class MppFactory extends Factory
{
    protected $model = Mpp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_plan' => 'MPP ' . $this->faker->jobTitle(),
            'departemen' => $this->faker->randomElement(['IT', 'HR', 'Finance', 'Marketing', 'Operations']),
            'jabatan' => $this->faker->jobTitle(),
            'jumlah_kebutuhan' => $this->faker->numberBetween(1, 10),
            'estimasi_gaji_min' => $this->faker->numberBetween(4000000, 6000000),
            'estimasi_gaji_max' => $this->faker->numberBetween(7000000, 15000000),
            'sla_hari' => $this->faker->numberBetween(14, 45),
            'target_waktu_absolut' => $this->faker->dateTimeBetween('+1 month', '+3 months')->format('Y-m-d'),
            'status' => $this->faker->randomElement([\App\Enums\MppStatus::DRAFT, \App\Enums\MppStatus::APPROVED, \App\Enums\MppStatus::COMPLETED_CLOSED, \App\Enums\MppStatus::CLOSED]),
            'note' => $this->faker->sentence(),
            'last_activity_at' => now(),
        ];
    }
}
