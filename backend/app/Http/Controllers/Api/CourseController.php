<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    // GET /api/v1/courses

    /**
     * @OA\Get(
     *     path="/api/v1/courses",
     *     summary="List all courses",
     *     tags={"Courses"},
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of courses",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function index()
    {
        $courses = Course::with(['category', 'instructor'])->paginate(10);
        return response()->json($courses);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/courses/preview/{id}",
     *     summary="preview course",
     *     tags={"Courses"},
     *     @OA\Response(
     *         response=200,
     *         description="Preview info of the requested course",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */

    public function preview($id)
    {
        $course = Course::with([
            'category',
            'instructor',
            'sections' => function ($q) {
                $q->orderBy('order')->with([
                    'lessons:id,section_id,title,order'
                ]);
            }
        ])->findOrFail($id);

        return response()->json($course);
    }

    // GET /api/v1/courses/{id}
     /**
     * @OA\Get(
     *     path="/api/v1/courses/{id}",
     *     summary="Get single course with details",
     *     tags={"Courses"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course details with sections and lessons",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Course not found")
     * )
     */
    public function show($id)
    {
        $course = Course::with(['category', 'instructor', 'sections.lessons'])->findOrFail($id);
        return response()->json($course);
    }

    // POST /api/v1/courses (Auth: instructor)
    /**
     * @OA\Post(
     *     path="/api/v1/courses",
     *     summary="Create a new course",
     *     tags={"Courses"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "category_id", "price"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="thumbnail_url", type="string", format="url")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Course created successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Only instructors can create courses"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'instructor') {
            return response()->json(['error' => 'Only instructors can create courses.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'thumbnail_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'instructor_id' => $user->id,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'thumbnail_url' => $request->thumbnail_url,
        ]);

        return response()->json($course, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/courses/{id}",
     *     summary="Update an existing course",
     *     tags={"Courses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the course to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="thumbnail_url", type="string", format="url")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $course = Course::findOrFail($id);

        if ($user->id !== $course->instructor_id) {
            return response()->json(['error' => 'Unauthorized to update this course'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'price' => 'sometimes|required|numeric|min:0',
            'thumbnail_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course->update($request->only([
            'title', 'description', 'category_id', 'price', 'thumbnail_url'
        ]));

        return response()->json($course);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/courses/{id}",
     *     summary="Delete a course",
     *     tags={"Courses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the course to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Course not found")
     * )
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $course = Course::findOrFail($id);

        if ($user->id !== $course->instructor_id) {
            return response()->json(['error' => 'Unauthorized to delete this course'], 403);
        }

        $course->delete();

        return response()->json(['message' => 'Course deleted successfully.']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/courses/mine",
     *     summary="List all courses created by the authenticated instructor",
     *     tags={"Courses"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of instructor's own courses",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */

    public function myCourses()
    {
        $user = Auth::user();

        if ($user->role !== 'instructor') {
            return response()->json(['error' => 'Only instructors can view their own courses'], 403);
        }

        $courses = Course::where('instructor_id', $user->id)->with('category')->get();

        return response()->json($courses);
    }
}

