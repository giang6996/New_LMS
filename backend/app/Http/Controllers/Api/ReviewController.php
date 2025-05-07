<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/review",
     *     summary="Submit or update a course review",
     *     tags={"Reviews"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"course_id", "rating"},
     *             @OA\Property(property="course_id", type="integer"),
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="comment", type="string", maxLength=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review submitted successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Course not completed"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */

    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $request->course_id)
            ->where('is_completed', true)
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'You must complete the course before reviewing.'], 403);
        }

        $review = Review::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $request->course_id
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
                'created_at' => now()
            ]
        );

        return response()->json(['message' => 'Review submitted successfully.', 'review' => $review]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reviews/course/{course_id}",
     *     summary="Get all reviews for a specific course",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="course_id",
     *         in="path",
     *         required=true,
     *         description="ID of the course",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of course reviews"),
     *     @OA\Response(response=404, description="Course not found")
     * )
     */
    public function reviewsByCourse($courseId)
    {
        $reviews = Review::where('course_id', $courseId)
            ->with('user:id,name') // Only show user id + name (not email!)
            ->get();

        return response()->json($reviews);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reviews/my",
     *     summary="Get all reviews by the authenticated user",
     *     tags={"Reviews"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of user's reviews"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function myReviews()
    {
        $user = Auth::user();
        $reviews = Review::where('user_id', $user->id)->with('course')->get();

        return response()->json($reviews);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/review/{id}",
     *     summary="Delete a review",
     *     tags={"Reviews"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Review ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Review deleted successfully"),
     *     @OA\Response(response=403, description="Unauthorized to delete this review"),
     *     @OA\Response(response=404, description="Review not found")
     * )
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $review = Review::findOrFail($id);

        if ($review->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully.']);
    }
}

