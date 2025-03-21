<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Professor;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // نمایش لیست دوره‌ها
    public function index()
    {
        $courses = Course::all();
        return view('courses.index', compact('courses'));
    }

    // نمایش فرم ایجاد دوره جدید
    public function create()
    {
        $professors = Professor::all();
        return view('courses.create', compact('professors'));
    }

    // ذخیره دوره جدید در پایگاه داده
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'professor_id' => 'required|exists:professors,id',
        ]);

        Course::create($validated);

        return redirect()->route('courses.index')->with('success', 'Course created successfully.');
    }

    // نمایش جزئیات یک دوره
    public function show(Course $course)
    {
        return view('courses.show', compact('course'));
    }

    // نمایش فرم ویرایش دوره
    public function edit(Course $course)
    {
        $professors = Professor::all();
        return view('courses.edit', compact('course', 'professors'));
    }

    // به‌روزرسانی اطلاعات دوره
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'professor_id' => 'required|exists:professors,id',
        ]);

        $course->update($validated);

        return redirect()->route('courses.index')->with('success', 'Course updated successfully.');
    }

    // حذف دوره از پایگاه داده
    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('courses.index')->with('success', 'Course deleted successfully.');
    }
}