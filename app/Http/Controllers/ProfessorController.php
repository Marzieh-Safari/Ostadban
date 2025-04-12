<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    public function search(Request $request)
    {
        $professor = Professor::where('full_name', 'LIKE', '%' . $request->query('name') . '%')->first();
    
        if ($professor) {
            $professor->increment('search_count');
            return response()->json($professor);
        }

        return response()->json(['message' => 'Professor not found'], 404);
    }

    public function mostSearched()
    {
        try {
            $professors = Professor::where('type', 'professor')
                                ->orderByDesc('search_count')
                                ->limit(10)
                                ->get(['id', 'full_name', 'search_count']);

            return response()->json($professors);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Professor::where('type', 'professor');
        $sortedByRating = Professor::getSortedByRating();

        if ($request->has('search')) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('sort')) {
            $query->orderBy($request->sort, $request->get('direction', 'asc'));
        }

        $professors = $query->get();

        if ($request->expectsJson()) {
            return response()->json([
                'sorted_by_rating' => $sortedByRating,
                'filtered_professors' => $professors
            ], 200);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'faculty_number' => 'required|string|max:255|unique:users,faculty_number',
            'password' => 'required|string|min:8'
        ]);

        $professor = Professor::create(array_merge($validated, ['type' => 'professor']));

        if ($request->expectsJson()) {
            return response()->json($professor, 201);
        }
    }

    public function show(Professor $professor, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json($professor, 200);
        }
    }
    public function guestShow($id)
    {
        // یافتن استاد با روابط مرتبط + فیلتر type
        $professor = Professor::where('type', 'professor')
            ->with(['courses' => function($query) {
                $query->select('id', 'title', 'faculty_number');
            }])
            ->select('id', 'full_name', 'username', 'department', 'average_rating', 'faculty_number', 'created_at')
            ->find($id);
    
        // اگر استاد پیدا نشد
        if (!$professor) {
            return response()->json([
                'success' => false,
                'message' => 'استاد مورد نظر یافت نشد'
            ], 404);
        }
    
        // فرمت‌بندی پاسخ
        $formattedCourses = $professor->courses->map(function($course) {
            return [
                'course_id' => $course->id,
                'title' => $course->title,
            ];
        });
    
        return response()->json([
            'success' => true,
            'data' => [
                'professor_id' => $professor->id,
                'full_name' => $professor->full_name,
                'username' => $professor->username,
                'department' => $professor->department,
                'average_rating' => $professor->average_rating,
                'courses' => $formattedCourses,
                'courses_count' => $professor->courses->count()
            ],
            'message' => 'اطلاعات استاد با موفقیت دریافت شد'
        ]);
    }
    public function guestIndex()
    {
        $professors = Professor::where('type', 'professor')
            ->with(['courses' => function($query) {
                $query->select('id', 'title', 'faculty_number');
            }])
            ->orderBy('average_rating', 'desc')
            ->get()
            ->map(function($professor) {
                return [
                    'id' => $professor->id,
                    'full_name' => $professor->full_name,
                    'username' => $professor->username,
                    'department' => $professor->department,
                    'average_rating' => $professor->average_rating,
                    'courses' => $professor->courses->map(function($course) {
                        return [
                            'id' => $course->id,
                            'title' => $course->title
                        ];
                    })
                ];
            });

        return response()->json($professors);
    }
}