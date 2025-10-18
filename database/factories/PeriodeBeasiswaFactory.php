<?php

namespace Database\Factories;

use App\Models\Beasiswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PeriodeBeasiswa>
 */
class PeriodeBeasiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mulaiDaftar = now()->subDays(rand(5, 15));
        $akhirDaftar = now()->addDays(rand(10, 30));
        $beasiswa = Beasiswa::factory()->create();
        $year = $this->faker->numberBetween(2025, 2026);

        return [
            'beasiswa_id' => $beasiswa->id,
            'nama_periode' => $beasiswa->nama_beasiswa . ' Periode ' . $year . '/' . $year + 1 . ' ' . $this->faker->randomElement(['Ganjil', 'Genap']),
            'besar_beasiswa' => $this->faker->numberBetween(3000000, 12000000), // Besaran tunjangan
            'tanggal_mulai_daftar' => $mulaiDaftar,
            'tanggal_akhir_daftar' => $akhirDaftar,
            'is_aktif' => true,

            // Data JSON untuk Repeater Persyaratan
            // 'persyaratans_json' => json_encode([
            //     [
            //         'tipe' => 'ipk_min',
            //         'nilai_min' => 3.00,
            //     ],
            //     [
            //         'tipe' => 'semester_min',
            //         'nilai_min' => $this->faker->numberBetween(3, 5),
            //     ],
            // ]),
        ];
    }
}
