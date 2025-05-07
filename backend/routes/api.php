<?php

use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CartController;

Route::prefix('v1')->group(function () {
    // Public endpoints
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{id}', [CourseController::class, 'show']);
    Route::get('courses/preview/{id}', [CourseController::class, 'preview']);

    // Auth-protected endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('courses', [CourseController::class, 'store']);
        Route::post('enroll', [EnrollmentController::class, 'store']);
        Route::get('my-courses', [EnrollmentController::class, 'myCourses']);

        Route::post('lesson-progress', [ProgressController::class, 'update']);
        Route::get('progress/{course_id}', [ProgressController::class, 'show']);

        Route::get('course-content/{id}', [ContentController::class, 'show']); // Sections + Lessons
        Route::put('courses/{id}', [CourseController::class, 'update']);
        Route::delete('courses/{id}', [CourseController::class, 'destroy']);
        Route::get('courses/mine', [CourseController::class, 'myCourses']); 

        // Section routes
        Route::post('sections', [SectionController::class, 'store']);
        Route::put('sections/{id}', [SectionController::class, 'update']);
        Route::delete('sections/{id}', [SectionController::class, 'destroy']);

        // Lesson routes
        Route::post('lessons', [LessonController::class, 'store']);
        Route::put('lessons/{id}', [LessonController::class, 'update']);
        Route::delete('lessons/{id}', [LessonController::class, 'destroy']);

        Route::post('review', [ReviewController::class, 'store']);
        Route::get('reviews/course/{course_id}', [ReviewController::class, 'show']); // By Course
        Route::get('reviews/my', [ReviewController::class, 'myReviews']);
        Route::delete('review/{id}', [ReviewController::class, 'destroy']);

        Route::post('checkout', [PaymentController::class, 'payment']);

        Route::post('cart/add', [CartController::class, 'add']);
        Route::get('cart', [CartController::class, 'index']);
        Route::delete('cart/{course_id}', [CartController::class, 'remove']);
    });
});
