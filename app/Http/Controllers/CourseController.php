<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User; 
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
        $professorUsername = $request->query('professor_username'); 
        $search = $request->query('course_title');
        $sortBy = $request->query('sort_by');


        $query = Course::with(['professors' => function($query) {
            $query->select('users.id', 'users.full_name', 'users.username'); 
        }])
        ->select('courses.id', 'courses.title', 'courses.slug', 'courses.description', 'courses.comments_count','courses.department','courses.department_id');


        if ($departmentId) {
            $query->where('courses.department_id', $departmentId);
        }

        if ($professorUsername) {
            $query->whereHas('professors', function($q) use ($professorUsername) {
                $q->where('users.username', $professorUsername); 
            });
        }


        if ($search && mb_strlen($search) >= 3) {
            $query->where('courses.title', 'LIKE', "%{$search}%");
        }

 
        if ($sortBy === 'most_commented') {
            $query->orderBy('comments_count', 'desc');
        }


        $courses = $query->paginate($perPage, ['*'], 'page', $page);


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


    public function show($slug)
    {

        $course = Course::with(['professors' => function($query) {
            $query->select(
                'users.id',
                'users.full_name',
                'users.username',
                'users.department',
                'users.avatar',
                'course_professor.average_rating'
            );
        }])->where('slug', $slug)->firstOrFail();


        $professors = $course->professors->map(function($professor) use ($course) {
            return [
                'avatar' => $professor->avatar ?? null,
                'department' => $professor->department,
                'username' => $professor->username,
                'full_name' => $professor->full_name,
                'average_rating' => $professor->pivot->average_rating,
                'comments_count' => $course->feedbacks()
                    ->where('professor_id', $professor->id)
                    ->count()
            ];
        });


        return response()->json([
            'success' => true,
            'data' => [ 
                'id' => $course->id,   
                'avatar' => $course->avatar ?? null,
                'comments_count' => $course->comments_count,
                'department' => $course->department,
                'title' => $course->title,
                'description' => $course->description,
                'slug' => $course->slug,
                'professors' => $professors
            ]
        ]);
    }

}