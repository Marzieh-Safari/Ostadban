<?php

use App\Http\Controllers\UserController; 
use Illuminate\Support\Facades\Route;   
use App\Http\Controllers\FeedbackController;  
use App\Http\Controllers\CourseController;  
use App\Http\Controllers\ProfessorController; 
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminSystemController;
use App\Models\Student;
use App\Models\Professor;
use Illuminate\Http\Request;

Route::get('/search', [CourseController::class, 'searchAll']);
Route::get('/feedback/guest', [FeedbackController::class, 'guestIndex']);
Route::get('/courses/guest', [CourseController::class, 'guestIndex']);
Route::get('/courses/guest/{id}', [CourseController::class, 'guestShow']);
Route::get('/api/guest/professor', [ProfessorController::class, 'guestIndex']);// نمایش لیست اساتید برای مهمان
Route::get('/api/guest/professor/{id}', [ProfessorController::class, 'guestShow']);// نمایش اطلاعات یک استاد مشخص برای مهمان
Route::get('/professor', [ProfessorController::class, 'index']);
Route::get('/api/course', [CourseController::class, 'index']); // لیست دوره‌ها برای کاربران
Route::get('/api/course/{course}', [CourseController::class, 'show']); // جزئیات یک دوره خاص
Route::get('/api/guest/course', [CourseController::class, 'guestIndex']); // لیست دوره‌ها برای مهمان‌ها
Route::resource('course', CourseController::class);
Route::apiResource('student', StudentController::class);
Route::apiResource('admin', AdminSystemController::class); 
Route::resource('feedback', FeedbackController::class);  
Route::resource('professor', ProfessorController::class);  