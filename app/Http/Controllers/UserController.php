<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // نمایش تمام کاربران براساس نوع (admin, professor, student) با Pagination
   public function index(Request $request)
{
    try {
        $type = $request->query('type');
        
        $users = User::where('type', $type) // شرط type فقط برای users
            ->with(['courses' => function($query) {
                $query->select('id', 'title', 'slug', 'course_code', 'professor_id');
            }])
            ->paginate(10)
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'full_name' => $user->full_name,
                    'department' => $user->department,
                    'role' => $user->type,
                    'IS_BOARD_MEMBER' => (bool)$user->is_board_member,
                    'average_rating' => (float)$user->average_rating,
                    'teaching_experience' => (int)$user->teaching_experience,
                    'comments_count' => (int)$user->comments_count,
                    'courses' => $user->courses->map(function($course) {
                        return [
                            'title' => $course->title,
                            'slug' => $course->slug,
                            'course_code' => $course->code // توجه: از alias استفاده شده
                        ];
                    })
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطایی رخ داد.',
            'error' => $e->getMessage()
        ], 500);
    }
}
    // ایجاد کاربر جدید
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:admin,professor,student',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        try {
            $user = User::create([
                'type' => $validated['type'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'کاربر با موفقیت ایجاد شد',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطایی در ایجاد کاربر رخ داد.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // نمایش اطلاعات یک کاربر خاص براساس ID
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر یافت نشد.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    // به‌روزرسانی اطلاعات کاربر
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'username' => 'nullable|string|max:255|unique:users,username,' . $id,
                'email' => 'nullable|email|unique:users,email,' . $id,
                'password' => 'nullable|string|min:8',
            ]);

            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'اطلاعات کاربر با موفقیت به‌روزرسانی شد',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی اطلاعات کاربر.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // حذف کاربر
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'کاربر با موفقیت حذف شد',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف کاربر.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function mostSearchedProfessors(Request $request)
{
    try {
        // گرفتن لیست اساتید مرتب شده بر اساس تعداد جست‌وجو
        $professors = User::where('type', 'professor') // فقط کاربران با نوع "professor"
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
    public function searchDepartments(Request $request)
{
    $searchQuery = $request->input('name'); // نام استاد برای جستجو

    $users = User::where('type', 'professor') // فقط اساتید
        ->where('full_name', 'LIKE', '%' . $searchQuery . '%') // جستجوی جزئی
        ->select('full_name', 'department') // فقط نام و دپارتمان
        ->get();

    return response()->json([
        'success' => true,
        'data' => $users,
    ]);
}

public function topRatedProfessors(Request $request, $limit = 10)
{
    try {
        // اعتبارسنجی مقدار limit
        $limit = $limit ?? $request->input('limit', 10);
        $limit = min(50, max(1, (int)$limit)); // حداکثر 50 نتیجه
        
        $professors = User::where('type', 'professor')
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
                'teaching_experience',
                'comments_count',
                'is_board_member'
            ]);
        
        // تبدیل داده‌ها به فرمت مناسب
        $transformedProfessors = $professors->map(function ($professor) {
            return [
                'id' => $professor->id,
                'name' => $professor->full_name,
                'department' => $professor->department,
                'rating' => (float) number_format($professor->average_rating, 1),
                'experience' => $professor->teaching_experience,
                'reviews_count' => $professor->comments_count,
                'is_board_member' => (bool) $professor->is_board_member,
                'courses_count' => $professor->courses->count() ?? 0
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