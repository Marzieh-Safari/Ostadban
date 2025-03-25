<?php

use App\Http\Controllers\UserController; 
use Illuminate\Support\Facades\Route;   
use App\Http\Controllers\FeedbackController;  
use App\Http\Controllers\CourseController;  
use App\Http\Controllers\ProfessorController; 
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminSystemController;


Route::get('/search', [CourseController::class, 'searchAll']);
Route::get('/feedbacks/guest', [FeedbackController::class, 'guestIndex']);
Route::get('/courses/guest', [CourseController::class, 'guestIndex']);
Route::get('/courses/guest/{id}', [CourseController::class, 'guestShow']);
Route::get('/api/guest/professors', [ProfessorController::class, 'guestIndex']);// نمایش لیست اساتید برای مهمان
Route::get('/api/guest/professors/{id}', [ProfessorController::class, 'guestShow']);// نمایش اطلاعات یک استاد مشخص برای مهمان
Route::get('/professors', [ProfessorController::class, 'index']);
Route::get('/api/courses', [CourseController::class, 'index']); // لیست دوره‌ها برای کاربران
Route::get('/api/courses/{course}', [CourseController::class, 'show']); // جزئیات یک دوره خاص
Route::get('/api/guest/courses', [CourseController::class, 'guestIndex']); // لیست دوره‌ها برای مهمان‌ها
Route::resource('courses', CourseController::class);
Route::apiResource('students', StudentController::class);
Route::apiResource('admins', AdminSystemController::class); 
Route::resource('feedback', FeedbackController::class);  
Route::resource('course', CourseController::class);  
Route::resource('professor', ProfessorController::class);  