<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\EnrollmentCode;
use App\Models\Payment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EnrollmentController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/enroll",
     *     summary="Enroll in a course (via QR or enrollment code)",
     *     tags={"Enrollment"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"course_id"},
     *             @OA\Property(property="course_id", type="integer"),
     *             @OA\Property(property="enrollment_code", type="string"),
     *             @OA\Property(property="payment_method", type="string", enum={"qrcode", "code"}),
     *             @OA\Property(property="payment_reference", type="string"),
     *             @OA\Property(property="verified", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Enrollment created",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=400, description="Invalid code or unverified payment"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */

    public function store(Request $request) // Single course enrollment
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'enrollment_code' => 'nullable|string',
            'payment_method' => 'nullable|in:qrcode,code',
            'payment_reference' => 'nullable|string',
            'verified' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $courseId = $request->course_id;
        $enrollmentCode = $request->enrollment_code;

        // If code is provided and valid
        if ($enrollmentCode) {
            $code = EnrollmentCode::where('code', $enrollmentCode)
                ->where('course_id', $courseId)
                ->where('is_used', false)
                ->first();

            if (!$code) {
                return response()->json(['error' => 'Invalid or already used enrollment code.'], 400);
            }

            // Mark code as used
            $code->update(['is_used' => true, 'used_at' => now()]);

            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
                'enrollment_code' => $enrollmentCode,
                'payment_status' => 'via_code',
                'enrolled_at' => now(),
            ]);
        } else {
            if (!$request->verified) {
                return response()->json(['error' => 'Payment must be verified before enrolling.'], 400);
            }

            // Record payment
            Payment::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
                'amount' => Course::findOrFail($courseId)->price,
                'method' => $request->payment_method ?? 'qrcode',
                'payment_reference' => $request->payment_reference,
                'verified' => true,
                'paid_at' => now()
            ]);

            // Create enrollment
            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
                'payment_status' => 'completed',
                'enrolled_at' => now(),
            ]);
        }

        return response()->json($enrollment, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/enroll-from-payment/{paymentId}",
     *     summary="Enroll into multiple purchased courses after verified payment",
     *     tags={"Enrollment"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="paymentId",
     *         in="path",
     *         required=true,
     *         description="ID of the verified payment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successfully enrolled into purchased courses"),
     *     @OA\Response(response=404, description="Payment not found or not verified")
     * )
     */
    public function enrollFromPayment($paymentId)
    {
        $user = Auth::user();

        $payment = Payment::where('id', $paymentId)
            ->where('user_id', $user->id)
            ->where('verified', true)
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found or not verified.'], 404);
        }

        $paymentItems = PaymentItem::where('payment_id', $payment->id)->get();

        foreach ($paymentItems as $item) {
            Enrollment::firstOrCreate([
                'user_id' => $user->id,
                'course_id' => $item->course_id
            ]);
        }

        return response()->json(['message' => 'Successfully enrolled into purchased courses.']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/my-courses",
     *     summary="Get all courses the user is enrolled in",
     *     tags={"Enrollment"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of enrolled courses",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */

    public function myCourses()
    {
        $user = Auth::user();
        $courses = $user->enrollments()->with('course')->get();
        return response()->json($courses);
    }
}

