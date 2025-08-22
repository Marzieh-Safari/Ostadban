<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User; // استفاده از مدل User به جای Professor
use Egulias\EmailValidator\Warning\DeprecatedComment;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function guestIndex(Request $request)
{
    try {
        $perPage = 12;
        $page = $request->query('page', 1);
        $departmentId = $request->query('department_id');
        $professorUsername = $request->query('professor_username'); // تغییر از professor_id به professor_username
        $search = $request->query('course_title');
        $sortBy = $request->query('sort_by');

        // شروع کوئری
        $query = Course::with(['professors' => function($query) {
            $query->select('users.id', 'users.full_name', 'users.username'); // اضافه کردن username به select
        }])
        ->select('courses.id', 'courses.title', 'courses.slug', 'courses.description', 'courses.comments_count','courses.department','courses.department_id');

        // اعمال فیلترها
        if ($departmentId) {
            $query->where('courses.department_id', $departmentId);
        }

        if ($professorUsername) {
            $query->whereHas('professors', function($q) use ($professorUsername) {
                $q->where('users.username', $professorUsername); // تغییر از id به username
            });
        }

        // جستجو
        if ($search && mb_strlen($search) >= 3) {
            $query->where('courses.title', 'LIKE', "%{$search}%");
        }

        // مرتب‌سازی
        if ($sortBy === 'most_commented') {
            $query->orderBy('comments_count', 'desc');
        }

        // صفحه‌بندی
        $courses = $query->paginate($perPage, ['*'], 'page', $page);

        // تبدیل به ساختار دلخواه
        $result = $courses->map(function($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->description,
                'comments_count' => $course->comments_count,
                'department' => $course->department,
                'professors' => $course->professors->map(function($professor) {
                    return [
                        'id' => $professor->id,
                        'full_name' => $professor->full_name
                    ];
                })->toArray()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
            'meta' => [
                'current_page' => $courses->currentPage(),
                'per_page' => $perPage,
                'total' => $courses->total(),
                'last_page' => $courses->lastPage(),
                'from' => $courses->firstItem(),
                'to' => $courses->lastItem(),
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطا در دریافت لیست دوره‌ها.',
            'error' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}


}