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


Route::get('/api/professor/most-searched', [ProfessorController::class, 'mostSearched']);
Route::get('/api/search', [CourseController::class, 'searchAll']);
Route::get('/api/feedback', [FeedbackController::class, 'guestIndex']);
Route::get('/api/course/{id}', [CourseController::class, 'show']);
Route::get('/api/professor', [ProfessorController::class, 'index']);// نمایش لیست اساتید برای مهمان
Route::get('/api/professor/{id}', [ProfessorController::class, 'show']);// نمایش اطلاعات یک استاد مشخص برای مهمان
//Route::get('/professor', [ProfessorController::class, 'index']);
//Route::get('/course/guest', [CourseController::class, 'guestIndex']);
Route::get('/api/course', [CourseController::class, 'guestIndex']); // لیست دوره‌ها برای کاربران
//Route::get('/api/course/{course}', [CourseController::class, 'show']); // جزئیات یک دوره خاص
Route::resource('course', CourseController::class);
Route::apiResource('student', StudentController::class);
Route::apiResource('admin', AdminSystemController::class); 
Route::resource('feedback', FeedbackController::class);  
Route::resource('professor', ProfessorController::class); 
//Route::get('/api/professor', [ProfessorController::class, 'index'])->name('professor.index'); 