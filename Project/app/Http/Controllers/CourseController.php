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
        $course = Course::with('professor')->get(); // همراه با اطلاعات استاد

        // اگر درخواست از API باشد، داده‌ها را به صورت JSON بازگردانید
        if ($request->expectsJson()) {
            return response()->json($course, 200);
        }

    }

    // نمایش فرم ایجاد دوره جدید
    public function create()
    {
        $professor = Professor::all();
        return view('course.create', compact('professor'));
    }

    // ذخیره دوره جدید در پایگاه داده
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'faculty_number' => 'required|exists:professor,id',
        ]);

        Course::create($validated);

        return redirect()->route('course.index')->with('success', 'Course created successfully.');
    }

    // نمایش جزئیات یک دوره (برای وب و API)
    public function show(Course $course, Request $request)
    {
        $course->load('professor'); // بارگذاری اطلاعات استاد مرتبط

        // اگر درخواست از API باشد، داده‌ها را به صورت JSON بازگردانید
        if ($request->expectsJson()) {
            return response()->json($course, 200);
        }

    }

    // نمایش فرم ویرایش دوره
    public function edit(Course $course)
    {
        $professor = Professor::all();
        return view('course.edit', compact('course', 'professor'));
    }

    // به‌روزرسانی اطلاعات دوره
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'faculty_number' => 'required|exists:professor,id',
        ]);

        $course->update($validated);

        return redirect()->route('course.index')->with('success', 'Course updated successfully.');
    }

    // حذف دوره از پایگاه داده
    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('course.index')->with('success', 'Course deleted successfully.');
    }

    // نمایش لیست دوره‌ها برای مهمان‌ها (API)
    public function guestIndex()
    {
        try {
            $courses = Course::with(['professor' => function($query) {
                $query->select('id', 'name'); // فقط فیلدهای مورد نیاز
            }])->get(['id', 'title', 'description', 'faculty_number']);
    
            return response()->json([
                'success' => true,
                'data' => $courses
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // نمایش جزئیات یک دوره برای مهمان‌ها (API)
    public function guestShow($id, Request $request)
    {
    $course = Course::with('professor')->findOrFail($id); // بارگذاری اطلاعات دوره و استاد مرتبط

    // اگر درخواست از API باشد، داده‌ها را به صورت JSON بازگردانید
    if ($request->expectsJson()) {
        return response()->json($course, 200);
    }

    // اگر درخواست از وب باشد، می‌توانید یک پیام خطا یا 404 برگردانید
    return response()->json(['error' => 'Unauthorized access'], 403);
    }

// جستجوی کلی برای دوره‌ها و اساتید (API)
    public function searchAll(Request $request)
    {
    $query = $request->input('query'); // دریافت کلمه کلیدی جستجو

    // جستجو در دوره‌ها
    $course = Course::with('professor')
        ->where('title', 'like', '%' . $query . '%')
        ->orWhere('description', 'like', '%' . $query . '%')
        ->get();

    // جستجو در اساتید
    $professor = Professor::where('name', 'like', '%' . $query . '%')->get();

    // ترکیب نتایج
    $results = [
        'course' => $course,
        'professor' => $professor,
    ];

    // اگر درخواست از API باشد، داده‌ها را به صورت JSON بازگردانید
    if ($request->expectsJson()) {
        return response()->json($results, 200);
    }

    }
}