<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User; // استفاده از مدل User به جای Professor
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // نمایش لیست دوره‌ها با Pagination (API و وب)
    public function index(Request $request)
    {
        try {
            $courses = Course::with('professor')->paginate(10);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $courses,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت لیست دوره‌ها.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // نمایش فرم ایجاد دوره جدید (فقط برای وب)
    public function create()
    {
        try {
            $professors = User::where('type', 'professor')->get(); // گرفتن لیست اساتید
        } catch (\Exception $e) {
            return redirect()->route('course.index')->withErrors('خطا در بارگذاری فرم ایجاد دوره.');
        }
    }

    // ذخیره دوره جدید در پایگاه داده
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'faculty_number' => 'required|exists:users,id', // بررسی وجود استاد در جدول کاربران
        ]);

        try {
            Course::create($validated);

            return redirect()->route('course.index')->with('success', 'دوره با موفقیت ایجاد شد.');
        } catch (\Exception $e) {
            return redirect()->route('course.create')->withErrors('خطا در ذخیره‌سازی دوره.');
        }
    }

    // نمایش جزئیات یک دوره (API و وب)
    public function show(Course $course, Request $request)
    {
        try {
            $course->load('professor');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $course,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در نمایش جزئیات دوره.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // نمایش فرم ویرایش دوره (فقط برای وب)
    public function edit(Course $course)
    {
        try {
            $professors = User::where('type', 'professor')->get(); // گرفتن لیست اساتید
        } catch (\Exception $e) {
            return redirect()->route('course.index')->withErrors('خطا در بارگذاری فرم ویرایش دوره.');
        }
    }

    // به‌روزرسانی اطلاعات دوره
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'faculty_number' => 'required|exists:users,id', // بررسی وجود استاد در جدول کاربران
        ]);

        try {
            $course->update($validated);

            return redirect()->route('course.index')->with('success', 'دوره با موفقیت به‌روزرسانی شد.');
        } catch (\Exception $e) {
            return redirect()->route('course.edit', $course->id)->withErrors('خطا در به‌روزرسانی دوره.');
        }
    }

    // حذف دوره از پایگاه داده
    public function destroy(Course $course)
    {
        try {
            $course->delete();

            return redirect()->route('course.index')->with('success', 'دوره با موفقیت حذف شد.');
        } catch (\Exception $e) {
            return redirect()->route('course.index')->withErrors('خطا در حذف دوره.');
        }
    }

    // نمایش لیست دوره‌ها برای مهمان‌ها (API)
    public function guestIndex()
    {
        try {
            $courses = Course::select('title', 'slug', 'description')->get();

            return response()->json([
                'success' => true,
                'data' => $courses,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت لیست دوره‌ها.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // نمایش جزئیات یک دوره برای مهمان‌ها (API)
    public function guestShow($id)
    {
        try {
            $course = Course::with('professor')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $course,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'دوره یافت نشد.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    // جستجوی کلی برای دوره‌ها و اساتید (API)
    public function searchAll(Request $request)
    {
        $query = $request->input('query');

        try {
            $courses = Course::with('professor')
                ->where('title', 'like', '%' . $query . '%')
                ->orWhere('description', 'like', '%' . $query . '%')
                ->get();

            $professors = User::where('type', 'professor')
                ->where('full_name', 'like', '%' . $query . '%')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'courses' => $courses,
                    'professors' => $professors,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در جستجو.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}