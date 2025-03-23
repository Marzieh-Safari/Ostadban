<?php

// app/Http/Controllers/StudentController.php
namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

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
            'email' => 'required|email|unique:students',
            'phone' => 'nullable|string',
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

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,' . $id,
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