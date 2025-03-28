<?php

// app/Http/Controllers/StudentController.php
namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use app\http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Hash;;

class StudentController extends Controller
{
    public function index()
    {
        return Student::all(); // نمایش تمام دانشجویان
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'email' => 'required|email|unique:student',
            'phone' => 'required|string|max:255',
            'major' => 'required|string|max:255',
        ]);

        $student = Student::create($validated);
        return response()->json($student, 201); // ایجاد دانشجو
    }

    public function show($id)
    {
        $student = Student::findOrFail($id);
        return response()->json($student); // نمایش دانشجوی خاص
    }
    

    public function profile(Request $request) {
        return response()->json($request->user());
    }
    
    public function updateProfile(UpdateProfileRequest $request) {
        $student = $request->user();
        $student->update($request->validated());
        return response()->json($student);
    }
    
    public function changePassword(Request $request) {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8'
        ]);
        
        $student = $request->user();
        
        if (!Hash::check($request->current_password, $student->password)) {
            return response()->json(['message' => 'Current password is wrong'], 400);
        }
        
        $student->update(['password' => Hash::make($request->new_password)]);
        return response()->json(['message' => 'Password changed']);
    }
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'email' => 'required|email|unique:student,email,' . $id,
            'phone' => 'nullable|string',
            'major' => 'required|string|max:255',
        ]);

        $student->update($validated);
        return response()->json($student); // به‌روزرسانی دانشجو
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        return response()->json(['message' => 'Student deleted']); // حذف دانشجو
    }
}