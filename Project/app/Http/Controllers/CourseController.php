<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Professor;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // نمایش لیست دوره‌ها (برای وب و API)
    public function index(Request $request)
    {
        $courses = Course::with('professor')->get(); // همراه با اطلاعات استاد

        // اگر درخواست از API باشد، داده‌ها را به صورت JSON بازگردانید
        if ($request->expectsJson()) {
            return response()->json($courses, 200);
        }

        // اگر درخواست از وب باشد، ویو را بازگردانید
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

    // نمایش جزئیات یک دوره (برای وب و API)
    public function show(Course $course, Request $request)
    {
        $course->load('professor'); // بارگذاری اطلاعات استاد مرتبط

        // اگر درخواست از API باشد، داده‌ها را به صورت JSON بازگردانید
        if ($request->expectsJson()) {
            return response()->json($course, 200);
        }

        // اگر درخواست از وب باشد، ویو را بازگردانید
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

    // نمایش لیست دوره‌ها برای مهمان‌ها (API)
    public function guestIndex()
    {
        $courses = Course::with('professor')->get(); // همراه با اطلاعات استاد
        return response()->json($courses, 200); // بازگرداندن داده‌ها در قالب JSON
    }
}