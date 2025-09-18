<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Course;
use Tymon\JWTAuth\Facades\JWTAuth;

class FeedbackController extends Controller
{
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
        
        //if (!$professorUsername && !$courseSlug) {
            //return response()->json([
                //'success' => false,
                //'message' => 'لطفاً professor_username یا course_slug را ارسال کنید',
                //'data' => []
            //], 400);
        //}

        $query = Feedback::with([
            'course:id,title,slug',
            'professor:id,full_name,username',
            'student:id,username,major,role'
        ]);

        $professorExists = true;
        $courseExists = true;

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


        if ($courseSlug) {
            $course = Course::where('slug', $courseSlug)->first();
            
            if (!$course) {
                $courseExists = false;
            } else {
                $query->where('course_id', $course->id);
            }
        }


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


        $feedbacks = $query->orderBy('created_at', 'desc')->get();


        if ($feedbacks->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'هیچ نظری یافت نشد',
                'data' => []
            ]);
        }


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

    public function userFeedbackList(Request $request)
{
    try {
        
        if (!$request->bearerToken()) {
            return response()->json([
                'success' => false,
                'message' => 'توکن احراز هویت ارائه نشده است'
            ], 401);
        }

        
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر احراز هویت نشده است'
            ], 401);
        }

        
        $professors = User::where('role', 'professor')
            ->with(['courses' => function ($query) {
                $query->select('id', 'title', 'course_code', 'professor_id', 'slug');
            }])
            ->select([
                'id',
                'username',
                'full_name',
                'department',
                'role',
                'is_board_member',
                'average_rating',
                'teaching_experience',
                'comments_count',
                'avatar',
            ])
            ->get();

        $professorResult = $professors->map(function ($professor) {
            return [
                'id' => $professor->id,
                'username' => $professor->username,
                'full_name' => $professor->full_name,
                'department' => $professor->department,
                'role' => $professor->role,
                'is_board_member' => (bool) $professor->is_board_member,
                'average_rating' => (float) $professor->average_rating,
                'teaching_experience' => (int) $professor->teaching_experience,
                'comments_count' => (int) $professor->comments_count,
                'avatar' => $professor->avatar,
                'courses' => $professor->courses->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'title' => $course->title,
                        'slug' => $course->slug,
                        'course_code' => $course->course_code,
                    ];
                })
            ];
        });

        
        $courses = Course::with(['professors' => function ($query) {
            $query->select('users.id', 'users.full_name', 'users.username');
        }])
            ->select('courses.id', 'courses.title', 'courses.slug', 'courses.description', 'courses.comments_count', 'courses.department', 'courses.department_id')
            ->get();

        $courseResult = $courses->map(function ($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->description,
                'comments_count' => $course->comments_count,
                'department' => $course->department,
                'professors' => $course->professors->map(function ($professor) {
                    return [
                        'id' => $professor->id,
                        'full_name' => $professor->full_name,
                    ];
                })->toArray()
            ];
        });

        
        $feedbacks = Feedback::with([
            'course:id,title,slug',
            'professor:id,full_name,username',
        ])
            ->where('student_id', $user->id) 
            ->orderBy('created_at', 'desc')
            ->get();

        $feedbackResult = $feedbacks->map(function ($feedback) {
            return [
                'id' => $feedback->id,
                'comment' => $feedback->comment,
                'rating' => $feedback->rating,
                'date' => $feedback->created_at->format('Y-m-d'),
                'course' => $feedback->course ? [
                    'title' => $feedback->course->title,
                    'slug' => $feedback->course->slug,
                ] : null,
                'professor' => $feedback->professor ? [
                    'full_name' => $feedback->professor->full_name,
                    'username' => $feedback->professor->username,
                ] : null,
            ];
        });

        
        return response()->json([
            'success' => true,
            'data' => [
                'professors' => $professorResult,
                'courses' => $courseResult,
                'feedbacks' => $feedbackResult,
            ],
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطایی در دریافت اطلاعات رخ داده است',
            'error' => env('APP_DEBUG') ? $e->getMessage() : null,
        ], 500);
    }
}

    public function addFeedback(Request $request)
{
    try {
        
        if (!$request->bearerToken()) {
            return response()->json([
                'success' => false,
                'message' => 'توکن احراز هویت ارائه نشده است'
            ], 401);
        }

        
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر احراز هویت نشده است'
            ], 401);
        }

        
        $validatedData = $request->validate([
            'comment' => 'required|string|max:500', 
            'rating' => 'required|numeric|min:1|max:5', 
            'professor_id' => 'required|exists:users,id', 
            'course_id' => 'required|exists:courses,id',
        ]);

        
        $feedback = Feedback::create([
            'comment' => $validatedData['comment'],
            'rating' => $validatedData['rating'],
            'professor_id' => $validatedData['professor_id'],
            'course_id' => $validatedData['course_id'],
            'student_id' => $user->id, 
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'نظر با موفقیت ثبت شد'
        ], 201); 

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطا در ثبت نظر',
            'error' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}


}