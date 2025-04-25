<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Section;
use App\Models\SectionProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProgressController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/v1/progress/{course_id}",
     *     summary="Get progress for a specific course",
     *     tags={"Progress"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="course_id",
     *         in="path",
     *         required=true,
     *         description="The course ID to get progress for",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returns progress data including percent, completed lessons and sections",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Enrollment not found")
     * )
     */

    public function show($courseId)
    {
        $user = Auth::user();

        $enrollment = Enrollment::with([
            'sectionProgress.section',
            'lessonProgress.lesson'
        ])->where('user_id', $user->id)
        ->where('course_id', $courseId)
        ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found.'], 404);
        }

        return response()->json([
            'progress_percent' => $enrollment->progress_percent,
            'is_completed' => $enrollment->is_completed,
            'completed_at' => $enrollment->completed_at,
            'sections' => $enrollment->sectionProgress,
            'lessons' => $enrollment->lessonProgress
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/lesson-progress",
     *     summary="Mark lesson as completed and update progress",
     *     tags={"Progress"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"lesson_id"},
     *             @OA\Property(property="lesson_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Progress updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="User not enrolled"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */

    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'lesson_id' => 'required|exists:lessons,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $lesson = Lesson::with('section')->findOrFail($request->lesson_id);
        $courseId = $lesson->section->course_id;

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'You are not enrolled in this course.'], 403);
        }

        $progress = LessonProgress::firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'lesson_id' => $lesson->id
            ],
            [
                'is_completed' => true,
                'completed_at' => now()
            ]
        );

        if (!$progress->is_completed) {
            $progress->update([
                'is_completed' => true,
                'completed_at' => now()
            ]);
        }

        // Update SectionProgress
        $section = $lesson->section;
        $sectionLessonsCount = $section->lessons()->count();
        $completedLessonsCount = LessonProgress::where('enrollment_id', $enrollment->id)
            ->whereIn('lesson_id', $section->lessons()->pluck('id'))
            ->where('is_completed', true)
            ->count();

        $sectionProgress = SectionProgress::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'section_id' => $section->id
            ],
            [
                'completed_lessons' => $completedLessonsCount,
                'total_lessons' => $sectionLessonsCount,
                'is_completed' => $completedLessonsCount === $sectionLessonsCount,
                'completed_at' => $completedLessonsCount === $sectionLessonsCount ? now() : null
            ]
        );

        // Update overall course progress
        $totalLessons = Lesson::whereIn('section_id',
            Section::where('course_id', $courseId)->pluck('id')
        )->count();

        $totalCompleted = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        $percent = $totalLessons > 0 ? round(($totalCompleted / $totalLessons) * 100, 2) : 0;
        $enrollment->progress_percent = $percent;
        if ($percent === 100) {
            $enrollment->is_completed = true;
            $enrollment->completed_at = now();
        }
        $enrollment->save();

        return response()->json([
            'message' => 'Lesson marked complete and progress updated.',
            'lesson_progress' => $progress,
            'section_progress' => $sectionProgress,
            'course_progress' => $enrollment->progress_percent
        ]);
    }
}
