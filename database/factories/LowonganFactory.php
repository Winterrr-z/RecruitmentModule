<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Lowongan;
use App\Models\Mpp;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lowongan>
 */
class LowonganFactory extends Factory
{
    protected $model = Lowongan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mpp_id' => Mpp::factory(),
            'jabatan' => $this->faker->jobTitle(),
            'departemen' => $this->faker->randomElement(['IT', 'HR', 'Finance', 'Marketing', 'Operations']),
            'estimasi_gaji_min' => $this->faker->numberBetween(4000000, 6000000),
            'estimasi_gaji_max' => $this->faker->numberBetween(7000000, 15000000),
            'expected_join_date' => $this->faker->dateTimeBetween('+1 month', '+3 months')->format('Y-m-d'),
            'deskripsi_pekerjaan' => $this->faker->paragraphs(3, true),
            'spesifikasi_kebutuhan' => $this->faker->paragraphs(2, true),
            'tipe_kerja' => $this->faker->randomElement(['full-time', 'contract']),
            'lokasi' => $this->faker->randomElement(['remote', 'on-site']),
            'application_deadline' => $this->faker->dateTimeBetween('+1 week', '+4 weeks')->format('Y-m-d'),
            'tampilkan_gaji' => $this->faker->boolean(),
            'status' => $this->faker->randomElement(['Published', 'Draft', 'Closed']),
            'kuota' => $this->faker->numberBetween(1, 5),
        ];
    }
}
