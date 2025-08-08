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
        $perPage = 12;
        $page = $request->query('page', 1);
        $departmentId = $request->query('department_id');
        $professorId = $request->query('professor_id');
        $search = $request->query('course_title');
        $sortBy = $request->query('sort_by');

        // شروع کوئری
        $query = Course::with(['professors' => function($query) {
            $query->select('id', 'department', 'full_name');
        }])
        ->select('id', 'title', 'slug', 'description', 'professor_id', 'avatar','comments_count');

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

             $query->whereHas('professors', function($q) use ($departmentId) {
             $q->where('department', $departmentId);
             });
        }

        if ($professorId) {
            $query->whereHas('professors', function($q) use ($professorId) {
                $q->where('users.id', $professorId);
            });
        }

        // جستجو با محدودیت‌های جدید
        if ($search) {
            $search = trim($search);

            if (preg_match('/[^پچجحخهعغفقثصضشسیبلاتنمکگوئدذرزطظژؤإأءًٌٍَُِّ\s]/u', $search) || 
                is_numeric($search) ||
                preg_match('/[#@$%^&*()_+=]/', $search)) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }
            
            if (mb_strlen($search) >= 3) {
                $query->where('title', 'LIKE', "%{$search}%");
            }
        }

        switch ($sortBy) {
            case 'most_commented':
                $query->orderBy('comments_count', 'desc');
                break;
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
                'comments_count' => $course->comments_count,
                'avatar' => $course->avatar,
                'department' => $course->professors->first()?->department ?? null,
                'professors' => $course->professors->map(function($professor) {
                    return [
                        'id' => $professor->id,
                        'full_name' => $professor->full_name
                    ];
                })->toArray()
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


}