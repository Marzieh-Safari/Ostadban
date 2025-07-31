<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User; // استفاده از مدل User به جای Professor
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function guestIndex(Request $request)
{
    try {
        // پارامترهای دریافت شده
        $perPage = 12; // ثابت
        $page = $request->query('page', 1);
        $departmentId = $request->query('department_id');
        $professorId = $request->query('professor_id');
        $search = $request->query('search');

        // شروع کوئری
        $query = Course::with(['professor' => function($query) {
            $query->select('id', 'department', 'full_name');
        }])
        ->select('id', 'title', 'slug', 'description', 'professor_id', 'avatar');

        // اعمال فیلترها
        if ($departmentId) {
            // ساخت لیست دپارتمان‌ها با آیدی
            $departments = User::whereNotNull('department')
                ->where('role', 'professor')
                ->distinct()
                ->pluck('department')
                ->values()
                ->map(function ($name, $index) {
                    return [
                        'id' => $index + 1,
                        'name' => $name
                    ];
                });

            $department = $departments->firstWhere('id', (int)$departmentId);
            
            if (!$department) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $query->whereHas('professor', function($q) use ($department) {
                $q->where('department', $department['name']);
            });
        }

        if ($professorId) {
            $query->where('professor_id', $professorId);
        }

        // جستجو (حداقل 3 حرف و معتبر)
        if ($search) {
            $search = trim($search);
            if (mb_strlen($search) < 3 || preg_match('/^[^\p{L}\p{N}]+$/u', $search)) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }
            $query->where('title', 'LIKE', "%{$search}%");
        }

        // صفحه بندی
        $courses = $query->paginate($perPage, ['*'], 'page', $page);

        // تبدیل به ساختار دلخواه
        $result = $courses->map(function($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->description,
                'avatar' => $course->avatar,
                'department' => $course->professor->department ?? null,
                'professor' => $course->professor ? [
                    'id' => $course->professor->id,
                    'full_name' => $course->professor->full_name
                ] : null
            ];
        });

        $response = [
            'success' => true,
            'data' => $result
        ];

        // اضافه کردن متا فقط اگر داده وجود داشته باشد
        if (!$result->isEmpty()) {
            $response['meta'] = [
                'current_page' => $courses->currentPage(),
                'per_page' => $perPage,
                'total' => $courses->total(),
                'last_page' => $courses->lastPage(),
                'from' => $courses->firstItem(),
                'to' => $courses->lastItem(),
            ];
        }

        return response()->json($response, 200);

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

            $professors = User::where('role', 'professor')
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