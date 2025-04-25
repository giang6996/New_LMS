<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $courses = [
            [
                'title' => 'Ahead somebody decision almost',
                'description' => 'Some start lead but store citizen. Pressure student moment view. Manager write chance become religious.',
                'category_id' => 3,
                'instructor_id' => 1,
                'price' => 28.29,
                'thumbnail_url' => 'https://placekitten.com/913/620',
            ],
            [
                'title' => 'Report measure',
                'description' => 'End party win next man safe recognize. Model yard four another. Activity national within central tree.',
                'category_id' => 4,
                'instructor_id' => 3,
                'price' => 23.98,
                'thumbnail_url' => 'https://placekitten.com/758/340',
            ],
            [
                'title' => 'At player time behavior especially',
                'description' => 'Everyone else need draw thank. Manage health during tend. Business forget fill bag above rich.',
                'category_id' => 3,
                'instructor_id' => 2,
                'price' => 33.26,
                'thumbnail_url' => 'https://placeimg.com/448/60/any',
            ],
            [
                'title' => 'When network appear',
                'description' => 'Positive politics serious. Stay possible study final soldier. Student will fine hospital world.',
                'category_id' => 2,
                'instructor_id' => 1,
                'price' => 23.74,
                'thumbnail_url' => 'https://www.lorempixel.com/44/329',
            ],
            [
                'title' => 'Tell both',
                'description' => 'Operation notice live. As person section soldier. Question whatever explain rich western company.',
                'category_id' => 5,
                'instructor_id' => 2,
                'price' => 33.54,
                'thumbnail_url' => 'https://www.lorempixel.com/81/1021',
            ],
            [
                'title' => 'Talk school end',
                'description' => 'Hold decade sign I heart down best light. Coach imagine lay mission.',
                'category_id' => 5,
                'instructor_id' => 2,
                'price' => 36.16,
                'thumbnail_url' => 'https://www.lorempixel.com/81/1023',
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}

