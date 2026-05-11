<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    Category::create([
            'name' => 'سيارت',
            'image' => 'random@example.com',
    ]);
    Category::create([
            'name' => 'عقارات',
            'image' => 'random@example.com',
    ]);
    Category::create([
            'name' => 'أجهزة',
            'image' => 'random@example.com',
    ]);
    Category::create([
            'name' => 'البسة',
            'image' => 'random@example.com',
    ]);
    Category::create([
            'name' => 'منظفات',
            'image' => 'random@example.com',
    ]);
    Category::create([
            'name' => 'طعام',
            'image' => 'random@example.com',
    ]);
    }
}
