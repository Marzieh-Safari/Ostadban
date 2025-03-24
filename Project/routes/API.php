<?php

use App\Http\Controllers\UserController; 
use Illuminate\Support\Facades\Route;   
use App\Http\Controllers\FeedbackController;  
use App\Http\Controllers\CourseController;  
use App\Http\Controllers\ProfessorController; 
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminSystemController;


Route::apiResource('students', StudentController::class);
Route::apiResource('admins', AdminSystemController::class);
Route::get('/professors', [ProfessorController::class, 'index']); 
Route::resource('feedback', FeedbackController::class);  
Route::resource('course', CourseController::class);  
Route::resource('professor', ProfessorController::class);  