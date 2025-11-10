<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Khách hàng mẫu',
            'email' => 'customer@example.com',
            'password' => Hash::make('12345678'),
            'role' => 1, // 1 = customer
            'address' => '123 Đường ABC, TP. HCM',
            'phone' => '0901234567',
        ]);

        User::create([
        'name' => 'Khách hàng thứ 2',
        'email' => 'customer2@example.com',
        'password' => Hash::make('12345678'),
        'role' => 1, // 1 = customer
        'address' => '456 Đường XYZ, Hà Nội',
        'phone' => '0987654321',
    ]);
    }
}
