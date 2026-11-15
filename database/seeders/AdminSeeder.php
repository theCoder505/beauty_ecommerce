<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@beautyecom.com'],
            [
                'name'     => 'Super Admin',
                'email'    => 'admin@beautyecom.com',
                'password' => Hash::make('Admin@12345'),
            ]
        );
    }
}

