<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/cart/add",
     *     summary="Add a course to cart",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"course_id"},
     *             @OA\Property(property="course_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Course added to cart"),
     *     @OA\Response(response=400, description="Course already in cart"),
     *     @OA\Response(response=404, description="Course not found")
     * )
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $courseId = $request->course_id;

        $exists = CartItem::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Course already in cart.'], 400);
        }

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'course_id' => $courseId,
        ]);

        return response()->json(['message' => 'Course added to cart.', 'cart_item' => $cartItem], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/cart",
     *     summary="View cart items",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of cart items")
     * )
     */
    public function index()
    {
        $user = Auth::user();
        $cartItems = CartItem::where('user_id', $user->id)
            ->with('course')
            ->get();

        return response()->json($cartItems);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/cart/{course_id}",
     *     summary="Remove a course from cart",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="course_id",
     *         in="path",
     *         required=true,
     *         description="ID of the course to remove from cart",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Course removed from cart"),
     *     @OA\Response(response=404, description="Cart item not found")
     * )
     */
    public function remove($courseId)
    {
        $user = Auth::user();
        $cartItem = CartItem::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();

        if (!$cartItem) {
            return response()->json(['error' => 'Cart item not found.'], 404);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Course removed from cart.']);
    }
}
