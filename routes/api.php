<?php


use Illuminate\Support\Facades\Route;   
use App\Http\Controllers\FeedbackController;  
use App\Http\Controllers\CourseController;  
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeedbackReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SearchController;

// مربوط به فلو یوزر
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:api');
//Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
Route::get('/user-feedback-list', [FeedbackController::class, 'userFeedbackList'])->middleware('auth:api');
Route::post('/add-feedback', [FeedbackController::class, 'addFeedback'])->middleware('auth:api');
Route::post('/feedback/report', [FeedbackReportController::class, 'reportFeedback']);
Route::middleware('auth:api')->group(function () {
    Route::post('/user/update', [UserController::class, 'update']);
});
//مربوط به فلو مهمان
Route::get('/search', [SearchController::class, 'search']);
Route::get('/feedbacks/{professor_username}', [FeedbackController::class, 'getProfessorFeedbacks']);
Route::get('/courses', [CourseController::class, 'guestIndex']); 
Route::get('/courses/{slug}', [CourseController::class, 'show']);
Route::get('/professors', [UserController::class, 'index']);
Route::get('/professors/{username}', [UserController::class, 'showProfessorByUsername']);
Route::get('/departments-list',[UserController::class, 'getDepartments']);
Route::get('/professors/top-rated', [UserController::class, 'topRatedProfessors']);
Route::get('/professors/top-rated/{limit}', [UserController::class, 'topRatedProfessors']);
Route::get('/feedbacks', [FeedbackController::class, 'getFeedbacks'])
    ->name('api.feedbacks.index');
