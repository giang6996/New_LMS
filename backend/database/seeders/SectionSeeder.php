<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    public function run()
    {
        $sectionTitles = ['Introduction', 'Core Concepts', 'Final Steps'];

        foreach (Course::all() as $course) {
            foreach ($sectionTitles as $index => $title) {
                Section::create([
                    'course_id' => $course->id,
                    'title' => $title,
                    'description' => 'This section covers ' . strtolower($title),
                    'order' => $index + 1,
                ]);
            }
        }
    }
}
