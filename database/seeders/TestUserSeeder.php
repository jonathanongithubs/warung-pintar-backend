<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Warung Test',
            'nama_usaha' => 'Warung Test',
            'email' => 'test@warung.com',
            'password' => Hash::make('password123'),
            'user_type' => 'umkm',
            'kategori' => 'Makanan',
            'phone' => '081234567890',
            'alamat' => 'Jl. Test No. 123, Jakarta',
        ]);
    }
}

