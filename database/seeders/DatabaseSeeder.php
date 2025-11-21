<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Mahasiswa;
use App\Models\PeriodeBeasiswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FakultasSeeder::class,
        ]);

        DB::table('roles')->insert([
            ['name' => UserRole::ADMIN],
            ['name' => UserRole::STAFF],
            ['name' => UserRole::MAHASISWA],
            ['name' => UserRole::PENGELOLA],
        ]);

        User::factory()->create([
            'name' => 'Admin',
            'nim' => 'admin',
            'role_id' => UserRole::ADMIN_ID,
        ]);

        User::factory()->create([
            'name' => 'Staff Akademik',
            'nim' => 'staffakademik',
            'role_id' => UserRole::MAHASISWA_ID,
        ]);

        DB::table('kategoris')->insert([
            [
                'nama_kategori' => 'Tidak mampu',
                'deskripsi' => fake()->paragraph(1),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Pemerintah',
                'deskripsi' => fake()->paragraph(1),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Swasta',
                'deskripsi' => fake()->paragraph(1),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Prestasi',
                'deskripsi' => fake()->paragraph(1),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Penelitian dan Pengabdian',
                'deskripsi' => fake()->paragraph(1),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Sekali terima',
                'deskripsi' => fake()->paragraph(1),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Sampai selesai',
                'deskripsi' => fake()->paragraph(1),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Satu periode',
                'deskripsi' => fake()->paragraph(1),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // PeriodeBeasiswa::factory(5)->create();
        // Mahasiswa::factory(50)->create();
    }
}
