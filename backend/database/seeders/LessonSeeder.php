<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;
use App\Models\Lesson;
use Illuminate\Support\Str;

class LessonSeeder extends Seeder
{
    public function run()
    {
        foreach (Section::all() as $section) {
            $lessonCount = rand(2, 4);
            for ($i = 1; $i <= $lessonCount; $i++) {
                Lesson::create([
                    'section_id' => $section->id,
                    'title' => "Lesson $i of {$section->title}",
                    'content' => Str::random(100),
                    'video_url' => "https://example.com/video/{$section->id}-{$i}",
                    'order' => $i,
                ]);
            }
        }
    }
}
