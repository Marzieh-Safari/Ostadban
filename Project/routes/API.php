<?php

use App\Http\Controllers\UserController; 
use Illuminate\Support\Facades\Route;   
use App\Http\Controllers\FeedbackController;  
use App\Http\Controllers\CourseController;  
use App\Http\Controllers\ProfessorController;  

Route::resource('user', UserController::class);  
Route::resource('feedback', FeedbackController::class);  
Route::resource('course', CourseController::class);  
Route::resource('professor', ProfessorController::class);  