<?php

use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\ReviewController;

Route::prefix('v1')->group(function () {
    // Public endpoints
    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{id}', [CourseController::class, 'show']);

    // Auth-protected endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('courses', [CourseController::class, 'store']);
        Route::post('enroll', [EnrollmentController::class, 'store']);
        Route::get('my-courses', [EnrollmentController::class, 'myCourses']);

        Route::get('course-content/{id}', [ContentController::class, 'show']); // Sections + Lessons
        Route::post('lesson-progress', [ProgressController::class, 'update']);
        Route::get('progress/{course_id}', [ProgressController::class, 'show']);
        Route::post('review', [ReviewController::class, 'store']);
    });
});
