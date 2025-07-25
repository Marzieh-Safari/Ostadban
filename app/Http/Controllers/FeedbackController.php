<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    // ذخیره نظر جدید
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'faculty_number' => 'required|exists:professors,id',
            'course_id' => 'required|exists:courses,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        try {
            Feedback::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'نظر با موفقیت ثبت شد.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت نظر.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // نمایش لیست نظرات با Pagination
    public function index(Request $request)
    {
        try {
            $feedbacks = Feedback::with(['professor', 'course'])->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $feedbacks,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت لیست نظرات.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // امتیازدهی به استاد
    public function rateProfessor(Request $request, $professorId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        try {
            $professor = User::findOrFail($professorId);

            if ($professor->role!== 'professor') {
                return response()->json([
                    'success' => false,
                    'message' => 'این کاربر استاد نیست.',
                ], 400);
            }

            $professor->ratings()->create([
                'student_id' => $request->user()->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'امتیاز با موفقیت ثبت شد.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت امتیاز.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}