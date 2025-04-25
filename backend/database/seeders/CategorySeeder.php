<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Programming'],
            ['name' => 'Design'],
            ['name' => 'Marketing'],
            ['name' => 'Business'],
            ['name' => 'Photography'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

