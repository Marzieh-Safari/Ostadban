<?php


use Illuminate\Support\Facades\Route;   
use App\Http\Controllers\FeedbackController;  
use App\Http\Controllers\CourseController;  
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RatingController;

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SearchController;

// AuthController
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
});
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
