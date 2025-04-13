<?php


use Illuminate\Support\Facades\Route;   
use App\Http\Controllers\FeedbackController;  
use App\Http\Controllers\CourseController;  
use App\Http\Controllers\ProfessorController; 
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminSystemController;
use App\Models\Student;
use App\Models\Professor;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RatingController;

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\UserController;

// AuthController
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/profile', [AuthController::class, 'profile']);

//Route::prefix('student')->group(function () {
    // احراز هویت
    //Route::post('/register', [AuthController::class, 'register']);
    //Route::post('/login', [AuthController::class, 'login']);
    
    // نیاز به احراز هویت
    //Route::middleware('auth:sanctum')->group(function () {
        //Route::post('/logout', [AuthController::class, 'logout']);
        
        // پروفایل
        //Route::get('/profile', [StudentController::class, 'profile']);
        //Route::put('/profile', [StudentController::class, 'updateProfile']);
        //Route::put('/change-password', [StudentController::class, 'changePassword']);
        
        // نظر و امتیاز
        //Route::post('/professor/{professor}/rate', [FeedbackController::class, 'rateProfessor']);
        //Route::get('/professor', [ProfessorController::class, 'index']);
        //Route::get('/professor/{professor}', [ProfessorController::class, 'show']);
    //});
//});


Route::get('/professor/most-searched', [UserController::class, 'mostSearchedProfessors']);
Route::get('/search', [CourseController::class, 'searchAll']);
Route::get('/feedback', [FeedbackController::class, 'Index']);
Route::get('/course/{id}', [CourseController::class, 'guestshow']);
Route::get('/professor', [UserController::class, 'index']);
Route::get('/professor/{id}', [UserController::class, 'Show']);
Route::get('/course', [CourseController::class, 'guestIndex']); // لیست دوره‌ها برای کاربران
//Route::resource('course', CourseController::class);
//Route::apiResource('student', StudentController::class);
//Route::apiResource('admin', AdminSystemController::class); 
//Route::resource('feedback', FeedbackController::class);  
//Route::resource('professor', ProfessorController::class); 
//Route::get('/api/professor', [ProfessorController::class, 'index'])->name('professor.index'); 