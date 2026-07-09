<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// DUA BARIS INI YANG BIKIN ERROR KALAU GAK ADA BRE 👇
use App\Models\Address; 
use App\Models\User;

class AddressSeeder extends Seeder
{
    public function run()
    {
        // Ambil user pertama yang ada di database
        $user = User::first(); 

        if ($user) {
            // 1. Alamat Utama (Jawa)
            Address::create([
                'user_id' => $user->id, // Udah gue benerin typo-nya pakai underscore
                'receiver_name' => 'Adam Muamar Kadafi',
                'phone' => '081234567890',
                'province' => 'JAWA BARAT',
                'city' => 'KAB. BOGOR',
                'district' => 'Gunung Sindur',
                'postal_code' => '16340',
                'detail_address' => 'Perumahan Griya Melina, Blok C3 No. 3, RT.2/RW.13, Desa Rawakalong',
                'is_primary' => true, 
            ]);

            // 2. Alamat Luar Jawa
            Address::create([
                'user_id' => $user->id,
                'receiver_name' => 'Bunda Rizkan',
                'phone' => '08319281928',
                'province' => 'KALIMANTAN SELATAN',
                'city' => 'KAB. TANAH BUMBU',
                'district' => 'Simpang Empat',
                'postal_code' => '72213',
                'detail_address' => 'Jalan Sampurna Gang Sampurna, Desa Hidayah Makmur',
                'is_primary' => false,
            ]);
        }
    }
}