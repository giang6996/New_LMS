<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::insert([
            [
                'name' => 'Alice Instructor',
                'email' => 'alice@learn.test',
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bob Instructor',
                'email' => 'bob@learn.test',
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Charlie Instructor',
                'email' => 'charlie@learn.test',
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Student One',
                'email' => 'student1@learn.test',
                'password' => Hash::make('password'),
                'role' => 'student',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Student Two',
                'email' => 'student2@learn.test',
                'password' => Hash::make('password'),
                'role' => 'student',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Student Three',
                'email' => 'student3@learn.test',
                'password' => Hash::make('password'),
                'role' => 'student',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
