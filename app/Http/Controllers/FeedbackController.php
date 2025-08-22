<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Course;

class FeedbackController extends Controller
{
    // ذخیره نظر جدید
    public function getProfessorFeedbacks($professorUsername, Request $request)
{
    try {
        $professor = User::where('username', $professorUsername)
            ->where('role', 'professor')
            ->firstOrFail();

        $query = Feedback::with([
            'course:id,title,slug',
            'professor:id,full_name,username',
            'student:id,username,major,role'
        ])
            ->where('professor_id', $professor->id)
            ->select([
                'id',
                'course_id',
                'professor_id',
                'student_id',
                'rating',
                'comment',
                'created_at as date'
            ]);

        if ($request->filled('course_id')) {
            $courseId = (int)$request->course_id;
            $query->where('course_id', $courseId);
        }

        $sort = $request->input('sort', 'newest');
        $query->orderBy('created_at', $sort === 'newest' ? 'desc' : 'asc');

        $feedbacks = $query->get();

        // تبدیل به ساختار خروجی
        $result = $feedbacks->map(function($feedback) {
            return [
                'id' => $feedback->id,
                'comment' => $feedback->comment,
                'rating' => $feedback->rating,
                'date' => $feedback->date,
                'course' => $feedback->course ? [
                    'title' => $feedback->course->title,
                    'slug' => $feedback->course->slug
                ] : null,
                'professor' => [
                    'full_name' => $feedback->professor->full_name,
                    'username' => $feedback->professor->username
                ],
                'student' => [
                    'username' => $feedback->student->username,
                    'major' => $feedback->student->major,
                    'role' => $feedback->student->role
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطا در دریافت نظرات',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function getFeedbacks(Request $request)
{
    try {
        $professorUsername = $request->query('professor_username');
        $courseSlug = $request->query('course_slug');
        
        if (!$professorUsername && !$courseSlug) {
            return response()->json([
                'success' => false,
                'message' => 'لطفاً professor_username یا course_slug را ارسال کنید',
                'data' => []
            ], 400);
        }

        $query = Feedback::with([
            'course:id,title,slug',
            'professor:id,full_name,username',
            'student:id,username,major,role'
        ]);

        $professorExists = true;
        $courseExists = true;

        // فیلتر بر اساس professor_username
        if ($professorUsername) {
            $professor = User::where('username', $professorUsername)
                ->where('role', 'professor')
                ->first();
            
            if (!$professor) {
                $professorExists = false;
            } else {
                $query->where('professor_id', $professor->id);
            }
        }

        // فیلتر بر اساس course_slug
        if ($courseSlug) {
            $course = Course::where('slug', $courseSlug)->first();
            
            if (!$course) {
                $courseExists = false;
            } else {
                $query->where('course_id', $course->id);
            }
        }

        // بررسی خطاها
        if (!$professorExists && !$courseExists) {
            return response()->json([
                'success' => false,
                'message' => 'هم professor_username و هم course_slug نامعتبر هستند',
                'data' => []
            ], 404);
        }

        if ($professorUsername && !$professorExists) {
            return response()->json([
                'success' => false,
                'message' => 'professor_username نامعتبر است',
                'data' => []
            ], 404);
        }

        if ($courseSlug && !$courseExists) {
            return response()->json([
                'success' => false,
                'message' => 'course_slug نامعتبر است',
                'data' => []
            ], 404);
        }

        // مرتب‌سازی از جدید به قدیم
        $feedbacks = $query->orderBy('created_at', 'desc')->get();

        // اگر داده‌ نداشتیم
        if ($feedbacks->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'هیچ نظری یافت نشد',
                'data' => []
            ]);
        }

        // تبدیل به ساختار خروجی
        $result = $feedbacks->map(function($feedback) {
            return [
                'id' => $feedback->id,
                'comment' => $feedback->comment,
                'rating' => $feedback->rating,
                'date' => $feedback->created_at->format('Y-m-d'),
                'course' => $feedback->course ? [
                    'title' => $feedback->course->title,
                    'slug' => $feedback->course->slug
                ] : null,
                'professor' => $feedback->professor ? [
                    'full_name' => $feedback->professor->full_name,
                    'username' => $feedback->professor->username
                ] : null,
                'student' => $feedback->student ? [
                    'username' => $feedback->student->username,
                    'major' => $feedback->student->major,
                    'role' => $feedback->student->role
                ] : null
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطا در دریافت نظرات',
            'error' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}

}