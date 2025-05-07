<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LessonController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/lessons",
     *     summary="Create a new lesson",
     *     tags={"Lessons"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"section_id", "title", "order"},
     *             @OA\Property(property="section_id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="video_url", type="string", format="url"),
     *             @OA\Property(property="order", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Lesson created successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'section_id' => 'required|exists:sections,id',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $section = Section::findOrFail($request->section_id);
        $course = $section->course;

        if (Auth::id() !== $course->instructor_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lesson = Lesson::create($request->all());

        return response()->json($lesson, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/lessons/{id}",
     *     summary="Update an existing lesson",
     *     tags={"Lessons"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Lesson ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="video_url", type="string", format="url"),
     *             @OA\Property(property="order", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Lesson updated successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Lesson not found")
     * )
     */   
    public function update(Request $request, $id)
    {
        $lesson = Lesson::findOrFail($id);
        $course = $lesson->section->course;

        if (Auth::id() !== $course->instructor_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lesson->update($request->only(['title', 'content', 'video_url', 'order']));
        return response()->json($lesson);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/lessons/{id}",
     *     summary="Delete a lesson",
     *     tags={"Lessons"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Lesson ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Lesson deleted successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Lesson not found")
     * )
     */

    public function destroy($id)
    {
        $lesson = Lesson::findOrFail($id);
        $course = $lesson->section->course;

        if (Auth::id() !== $course->instructor_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lesson->delete();
        return response()->json(['message' => 'Lesson deleted successfully.']);
    }
}

