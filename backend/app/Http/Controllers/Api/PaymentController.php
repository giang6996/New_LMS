<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\CartItem;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/checkout",
     *     summary="Checkout cart and create a payment",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="method", type="string", example="qrcode")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment created successfully, awaiting verification",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="payment", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Cart is empty"),
     *     @OA\Response(response=500, description="Payment process failed")
     * )
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Get all cart items
        $cartItems = CartItem::where('user_id', $user->id)->with('course')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        // Calculate total price
        $totalAmount = $cartItems->sum(function ($item) {
            return $item->course->price;
        });

        DB::beginTransaction();
        try {
            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $totalAmount,
                'method' => $request->method ?? 'qrcode', // Default to 'qrcode' if not provided
                'verified' => false
            ]);

            // Create payment items
            foreach ($cartItems as $item) {
                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'course_id' => $item->course_id,
                ]);
            }

            // Clear the cart
            CartItem::where('user_id', $user->id)->delete();

            DB::commit();

            return response()->json([
                'message' => 'Payment created. Please complete payment and verify.',
                'payment' => $payment
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Payment process failed.', 'details' => $e->getMessage()], 500);
        }
    }
}

