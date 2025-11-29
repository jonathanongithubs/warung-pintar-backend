<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InvestorSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Investor Test',
            'nama_usaha' => 'Investor Test',
            'email' => 'investor@test.com',
            'password' => Hash::make('password123'),
            'user_type' => 'investor',
            'kategori' => 'Investor',
            'phone' => '081234567891',
            'alamat' => 'Jl. Investasi No. 123, Jakarta',
        ]);
    }
}

