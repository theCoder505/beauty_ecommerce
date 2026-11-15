<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SettingSeeder::class,
            CategorySeeder::class,
            BlogSeeder::class,
        ]);
    }
}
