<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Course;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{

   public function index(Request $request)
{
    try {
        $role = $request->query('role', 'professor');
        $perPage = $request->query('per_page', 12);
        $sortBy = $request->query('sort_by', 'popular');
        $departmentId = $request->query('department_id');
        $courseSlug = $request->query('course_slug'); 
        $search = $request->query('search');
        $page = $request->query('page', 1);

        $query = User::where('role', $role)
            ->with(['courses' => function($query) {
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
            ]);


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
                $query->where('full_name', 'LIKE', "%{$search}%");
            }
        }


        if ($departmentId) {
            $departments = User::whereNotNull('department')
                ->where('role', 'professor')
                ->distinct()
                ->pluck('department');

            $departmentName = $departments->values()->get((int)$departmentId - 1);
            
            if (!$departmentName) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $query->where('department', $departmentName);
        }


        if ($courseSlug) {
            $query->whereHas('courses', function($q) use ($courseSlug) {
                $q->where('slug', $courseSlug); 
            });
        }


        switch ($sortBy) {
            case 'experienced':
                $query->orderBy('teaching_experience', 'desc');
                break;
            case 'most_commented':
                $query->orderBy('comments_count', 'desc');
                break;
            case 'popular':
            default:
                $query->orderBy('average_rating', 'desc');
                break;
        }

        $query->orderBy('id', 'asc');
        $users = $query->paginate($perPage, ['*'], 'page', $page);

        $result = $users->map(function($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'department' => $user->department,
                'role' => $user->role, 
                'is_board_member' => (bool)$user->is_board_member,
                'average_rating' => (float)$user->average_rating,
                'teaching_experience' => (int)$user->teaching_experience,
                'comments_count' => (int)$user->comments_count,
                'avatar' => $user->avatar,
                'courses' => $user->courses->map(function($course) {
                    return [
                        'id' => $course->id,
                        'title' => $course->title,
                        'slug' => $course->slug,
                        'course_code' => $course->course_code
                    ];
                })
            ];
        });

        $response = [
            'success' => true,
            'data' => $result
        ];

        if (!$result->isEmpty()) {
            $response['meta'] = [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ];
        }

        return response()->json($response);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطایی در سرور رخ داد.',
            'error' => $e->getMessage()
        ], 500);
    }
}



  public function getDepartments(Request $request)
{
    $departmentName = $request->query('department_name');
    
    $query = User::whereNotNull('department')
        ->where('role', 'professor')
        ->distinct()
        ->select('department');
    

    if ($departmentName) {
        $departmentName = trim($departmentName);
        if (preg_match('/[^پچجحخهعغفقثصضشسیبلاتنمکگوئدذرزطظژؤإأءًٌٍَُِّ\s]/u', $departmentName)) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
        

        if (mb_strlen($departmentName) >= 3) {
            $query->where('department', 'LIKE', "%{$departmentName}%");
        }
        
    }
    
    $departments = $query->pluck('department')
        ->values()
        ->map(function ($name, $index) {
            return [
                'id' => $index + 1,
                'name' => $name
            ];
        });
    
    return response()->json([
        'success' => true,
        'data' => $departments
    ]);
}



public function topRatedProfessors(Request $request, $limit = 10)
{
    try {

        $limit = $limit ?? $request->input('limit', 10);
        $limit = min(50, max(1, (int)$limit)); 
        
        $professors = User::where('role', 'professor')
            ->where('average_rating', '>', 0) 
            ->orderBy('average_rating', 'desc')
            ->take($limit)
            ->get([
                'id',
                'full_name',
                'department',
                'average_rating',
                'avatar',
            ]);
        

        $transformedProfessors = $professors->map(function ($professor) {
            return [
                'id' => $professor->id,
                'full_name' => $professor->full_name,
                'department' => $professor->department,
                'average_rating' => (float) number_format($professor->average_rating, 1),
                'avatar' => $professor ->avatar,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedProfessors,
            'meta' => [
                'total' => $professors->count(),
                'limit' => $limit
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطا در دریافت اساتید برتر',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function showProfessorByUsername($username)
    {
        try {
            $professor = User::with(['taughtCourses'])
                ->where('username', $username)
                ->where('role', 'professor')
                ->first([
                    'id',
                    'username',
                    'full_name',
                    'department',
                    'role',
                    'is_board_member',
                    'average_rating',
                    'teaching_experience',
                    'comments_count',
                    'avatar'
                ]);

            if (!$professor) {
                return response()->json([
                    'success' => false,
                    'message' => 'استاد مورد نظر یافت نشد'
                ], 404);
            }


            $response = [
                'id' => $professor->id,
                'username' => $professor->username,
                'full_name' => $professor->full_name,
                'department' => $professor->department,
                'role' => $professor->role,
                'is_board_member' => (bool)$professor->is_board_member,
                'average_rating' => (float)$professor->average_rating,
                'teaching_experience' => (int)$professor->teaching_experience,
                'comments_count' => (int)$professor->comments_count,
                'avatar' => $professor->avatar,
                'courses' => $professor->taughtCourses->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'title' => $course->title,
                        'slug' => $course->slug,
                        'course_code' => $course->course_code,
                        'avatar' => $course->avatar,
                        'comments_count' => $course->comments_count,
                        'average_rating' => $course->average_rating,
                        'department' => $course->department
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در پردازش درخواست',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


    public function update(Request $request)
{
    try {
       
        if (!$token = $request->bearerToken()) {
            return response()->json([
                'success' => false,
                'message' => 'توکن احراز هویت ارائه نشده است'
            ], 401);
        }

        
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'کاربری با این توکن یافت نشد'
                ], 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'توکن منقضی شده است',
                'error_code' => 'token_expired'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'توکن نامعتبر است',
                'error_code' => 'token_invalid'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در احراز هویت',
                'error_details' => $e->getMessage(),
                'error_code' => 'jwt_error'
            ], 500);
        }

        
        try {
            $validatedData = $request->validate([
                'password' => 'sometimes|required|string|min:8',
                'major' => 'nullable|string|max:255',
                'EntryYear' => 'nullable|string|min:1390|max:' . date('Y'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی داده‌ها',
                'errors' => $e->errors()
            ], 422);
        }

        
        $updated = false;
        
        if ($request->filled('password')) {
            $user->password = Hash::make($validatedData['password']);
            $updated = true;
        }

        if ($request->filled('major')) {
            $user->major = $validatedData['major'];
            $updated = true;
        }

        if ($request->filled('EntryYear')) {
            $user->EntryYear = $validatedData['EntryYear'];
            $updated = true;
        }

        
        if ($updated && !$user->save()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ذخیره‌سازی اطلاعات کاربر'
            ], 500);
        }

        
        return response()->json([
            'success' => true,
            'message' => $updated ? 'اطلاعات کاربر با موفقیت به‌روزرسانی شد' : 'هیچ تغییری اعمال نشد',
            'user' => [
                'id' => $user->id,
                'major' => $user->major,
                'EntryYear' => $user->EntryYear
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطای سرور در پردازش درخواست'
        ], 500);
    }
}

}