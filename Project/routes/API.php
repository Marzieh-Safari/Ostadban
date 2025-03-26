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

Route::post('/register/student', function (Request $request) {
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:students',
        'student_number' => 'required|string|max:20',
    ]);

    $student = Student::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'student_number' => $validatedData['student_number'],
        'is_approved' => false, // به صورت پیش‌فرض حساب تأیید نشده است
    ]);

    return response()->json(['message' => 'Registration successful. Awaiting approval.'], 201);
});

Route::post('/register/professor', function (Request $request) {
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:professors',
        'faculty_number' => 'required|string|max:20',
    ]);

    $professor = Professor::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'faculty_number' => $validatedData['faculty_number'],
        'is_approved' => false, // به صورت پیش‌فرض حساب تأیید نشده است
    ]);

    return response()->json(['message' => 'Registration successful. Awaiting approval.'], 201);
});

Route::post('/approve-user/{userType}/{id}', function ($userType, $id) {
    if ($userType === 'student') {
        $user = Student::findOrFail($id);
    } elseif ($userType === 'professor') {
        $user = Professor::findOrFail($id);
    } else {
        return response()->json(['message' => 'Invalid user type.'], 400);
    }

    $user->is_approved = true; // تغییر وضعیت به تأیید شده
    $user->save();

    return response()->json(['message' => 'User approved.'], 200);
});


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