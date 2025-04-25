<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;

// For OpenAPI annotation
/**
 * @OA\Get(
 *     path="/api/v1/course-content/{id}",
 *     summary="Get course content (sections and lessons)",
 *     tags={"Course Content"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Course ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of sections with nested lessons",
 *         @OA\JsonContent(type="array", @OA\Items(type="object"))
 *     ),
 *     @OA\Response(response=404, description="Course not found")
 * )
 */

class ContentController extends Controller
{
    public function show($id)
    {
        $course = Course::with(['sections' => function ($q) {
            $q->orderBy('order')->with(['lessons' => function ($q2) {
                $q2->orderBy('order');
            }]);
        }])->findOrFail($id);

        return response()->json($course->sections);
    }
}
