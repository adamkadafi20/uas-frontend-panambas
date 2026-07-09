<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    // Buat akun admin untuk login
    \App\Models\User::factory()->create([
        'name' => 'Admin Toko',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'), // Passwordnya nanti: admin123
        'role' => 2, // Biasanya 2 itu kode untuk Admin di starter kit ini
    ]);

    // Tetap buat kategori otomatis buat ngetes tampilan
    \App\Models\Category::factory(10)->create();
}
}
