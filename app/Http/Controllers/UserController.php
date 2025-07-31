<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Course;

class UserController extends Controller
{

   public function index(Request $request)
{
    try {
        $role = $request->query('role', 'professor');
        $perPage = $request->query('per_page', 12);
        $sortBy = $request->query('sort_by', 'popular');
        $departmentId = $request->query('department_id');
        $departmentName = $request->query('department_name');
        $courseId = $request->query('course_id');
        $courseTitle = $request->query('course_title');
        $search = $request->query('search');

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

        // اعمال فیلتر جستجو عمومی - اصلاح شده
        if ($search) {
            $search = trim($search);
            if (mb_strlen($search) < 3 || preg_match('/^[^\p{L}\p{N}]+$/u', $search)) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }
            $query->where('full_name', 'LIKE', "%{$search}%");
        }

        // فیلتر بر اساس دپارتمان (آیدی یا نام) - اصلاح شده
        if ($departmentId || $departmentName) {
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
            } elseif ($departmentName) {
                $departmentName = trim($departmentName);
                if (mb_strlen($departmentName) < 3 || preg_match('/^[^\p{L}\p{N}]+$/u', $departmentName)) {
                    return response()->json([
                        'success' => true,
                        'data' => []
                    ]);
                }
                $query->where('department', 'LIKE', "%{$departmentName}%");
            }
        }

        // فیلتر بر اساس دوره (آیدی یا عنوان) - اصلاح شده
        if ($courseId || $courseTitle) {
            if ($courseId) {
                $query->whereHas('courses', function($q) use ($courseId) {
                    $q->where('id', $courseId);
                });
            } elseif ($courseTitle) {
                $courseTitle = trim($courseTitle);
                if (mb_strlen($courseTitle) < 3 || preg_match('/^[^\p{L}\p{N}]+$/u', $courseTitle)) {
                    return response()->json([
                        'success' => true,
                        'data' => []
                    ]);
                }
                $query->whereHas('courses', function($q) use ($courseTitle) {
                    $q->where('title', 'LIKE', "%{$courseTitle}%");
                });
            }
        }

        // مرتب‌سازی
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
        $users = $query->paginate($perPage);

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

        // اضافه کردن متا فقط اگر داده وجود داشته باشد
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
            'message' => 'خطایی رخ داد.',
            'error' => $e->getMessage()
        ], 500);
    }
}
  public function getDepartments()
{
    $departments = User::whereNotNull('department')
        ->where('role', 'professor')
        ->distinct()
        ->pluck('department')
        ->values() // تبدیل به آرایه ایندکس‌دار
        ->map(function ($name, $index) {
            return [
                'id' => $index + 1, // شروع از 1 و افزایش ترتیبی
                'name' => $name
            ];
        });

    return response()->json([
        'success' => true,
        'data' => $departments
    ]);
}
    public function mostSearchedProfessors(Request $request)
{
    try {
        // گرفتن لیست اساتید مرتب شده بر اساس تعداد جست‌وجو
        $professors = User::where('role', 'professor') // فقط کاربران با نوع "professor"
            ->orderBy('search_count', 'desc') // مرتب‌سازی بر اساس تعداد جست‌وجو
            ->take(10) // گرفتن 10 استاد برتر
            ->get(['id', 'full_name', 'email', 'search_count']); // فقط ستون‌های لازم

        return response()->json([
            'success' => true,
            'data' => $professors,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطا در دریافت اطلاعات.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
    /**
 * دریافت نام دپارتمان بر اساس آیدی
 *
 * @param int $departmentId
 * @return \Illuminate\Http\JsonResponse
 */
public function getDepartmentName($departmentId)
{
    try {
        // دریافت تمام دپارتمان‌ها با همان منطق قبلی
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

        // پیدا کردن دپارتمان با آیدی مورد نظر
        $department = $departments->firstWhere('id', $departmentId);

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'دپارتمان با آیدی مورد نظر یافت نشد'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $department['id'],
                'name' => $department['name']
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطا در دریافت نام دپارتمان',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function topRatedProfessors(Request $request, $limit = 10)
{
    try {
        // اعتبارسنجی مقدار limit
        $limit = $limit ?? $request->input('limit', 10);
        $limit = min(50, max(1, (int)$limit)); // حداکثر 50 نتیجه
        
        $professors = User::where('role', 'professor')
            ->where('average_rating', '>', 0) // فقط اساتید با امتیاز
            //->withCount('feedbacks') // تعداد فیدبک‌ها
            ->orderBy('average_rating', 'desc')
            //->orderBy('feedbacks_count', 'desc') // در صورت تساوی امتیاز، تعداد فیدبک بیشتر
            ->take($limit)
            ->get([
                'id',
                'full_name',
                'department',
                'average_rating',
                'avatar',
            ]);
        
        // تبدیل داده‌ها به فرمت مناسب
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
}