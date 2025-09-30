<?php

namespace Database\Factories;

use App\Models\Beasiswa;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Beasiswa>
 */
class BeasiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lembaga = ['Kementerian Agama RI', 'Bank Indonesia', 'Pemerintah Provinsi Kaltim', 'UINSI Samarinda', 'Djarum Foundation'];
        $nama = ['Unggulan', 'Cendekia', 'Kaltim Tuntas', 'Prestasi Akademik', 'Riset Inovatif'];
        $startYear = $this->faker->numberBetween(2023, 2025);

        return [
            'nama_beasiswa' => 'Beasiswa ' . $this->faker->randomElement($nama),
            'lembaga_penyelenggara' => $this->faker->randomElement($lembaga),
            'besar_beasiswa' => $this->faker->randomElement([5000000, 7500000, 10000000, 12000000]),
            'periode' => $startYear . '/' . ($startYear + 1),
            'deskripsi' => $this->faker->paragraph(3),
        ];
    }

    // Ini adalah cara untuk menghubungkan beasiswa dengan kategori setelah dibuat
    public function configure()
    {
        return $this->afterCreating(function (Beasiswa $beasiswa) {
            // Ambil semua ID kategori yang ada
            $kategoriIds = Kategori::pluck('id')->toArray();

            // Pilih beberapa kategori secara acak (1-3 kategori)
            $randomKategoriIds = $this->faker->randomElements($kategoriIds, $this->faker->numberBetween(1, 3));

            // Hubungkan beasiswa dengan kategori yang dipilih
            $beasiswa->kategori()->attach($randomKategoriIds);
        });
    }
}
