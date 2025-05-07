<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/sections",
     *     summary="Create a new section",
     *     tags={"Sections"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"course_id", "title", "order"},
     *             @OA\Property(property="course_id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="order", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Section created successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course = Course::findOrFail($request->course_id);
        if (Auth::id() !== $course->instructor_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $section = Section::create($request->all());

        return response()->json($section, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/sections/{id}",
     *     summary="Update an existing section",
     *     tags={"Sections"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Section ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="order", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Section updated successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Section not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $section = Section::findOrFail($id);
        $course = $section->course;

        if (Auth::id() !== $course->instructor_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $section->update($request->only(['title', 'description', 'order']));
        return response()->json($section);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/sections/{id}",
     *     summary="Delete a section",
     *     tags={"Sections"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Section ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Section deleted successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Section not found")
     * )
     */
    public function destroy($id)
    {
        $section = Section::findOrFail($id);
        $course = $section->course;

        if (Auth::id() !== $course->instructor_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $section->delete();
        return response()->json(['message' => 'Section deleted successfully.']);
    }
}

