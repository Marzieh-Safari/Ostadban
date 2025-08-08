<?php


use Illuminate\Support\Facades\Route;   
use App\Http\Controllers\FeedbackController;  
use App\Http\Controllers\CourseController;  
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RatingController;

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\UserController;

// AuthController
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
//Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
//Route::middleware('auth:sanctum')->get('/profile', [AuthController::class, 'profile']);
Route::get('/search', [CourseController::class, 'searchAll']);
Route::get('/feedback', [FeedbackController::class, 'index']);
Route::get('/courses', [CourseController::class, 'guestIndex']); 
Route::get('/course/{id}', [CourseController::class, 'guestShow']);
Route::get('/professors', [UserController::class, 'index']);
Route::get('/professor/{id}', [UserController::class, 'show']);
Route::get('/departments-list',[UserController::class, 'getDepartments']);
Route::get('/professors/top-rated', [UserController::class, 'topRatedProfessors']);
Route::get('/professors/top-rated/{limit}', [UserController::class, 'topRatedProfessors']);
