<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    public function search(Request $request)
    {
        $professor = Professor::where('name', 'LIKE', '%' . $request->query('name') . '%')->first();
    
        if ($professor) {
        // افزایش مقدار search_count
            $professor->increment('search_count'); // معادل $professor->search_count += 1;
            $professor->save();
        }

    }

    // app/Http/Controllers/ProfessorController.php

    public function mostSearched()
    {
    try {
        $professor = Professor::orderByDesc('search_count')
                             ->limit(10)
                             ->get(['id', 'name', 'search_count']);

        return response()->json($professor);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Internal Server Error',
            'message' => $e->getMessage()
        ], 500);
    }
    }


    // نمایش لیست اساتید (برای وب و API)
    public function index(Request $request)
    {
        // جست‌وجو و مرتب‌سازی
        $query = Professor::query();
        $professor = Professor::getSortedByRating();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('sort')) {
            $query->orderBy($request->sort, $request->get('direction', 'asc'));
        }

        $professor = $query->get();

        // اگر درخواست از API باشد، داده‌ها را به صورت JSON بازگردانید
        if ($request->expectsJson()) {
            return response()->json($professor, 200);
        }

    }

    // نمایش فرم ایجاد استاد جدید
    public function create()
    {
        return view('professor.create');
    }

    // ذخیره استاد جدید در پایگاه داده
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'email' => 'required|email|unique:professor',
            'faculty_number'=>'required|string|max:255'
        ]);

        Professor::create($validated);

        return redirect()->route('professor.index')->with('success', 'Professor created successfully.');
    }

    // نمایش جزئیات یک استاد (برای وب و API)
    public function show(Professor $professor, Request $request)
    {
        // اگر درخواست از API باشد، داده‌ها را به صورت JSON بازگردانید
        if ($request->expectsJson()) {
            return response()->json($professor, 200);
        }

    }

    // نمایش فرم ویرایش استاد
    public function edit(Professor $professor)
    {
        return view('professor.edit', compact('professor'));
    }

    // به‌روزرسانی اطلاعات استاد
    public function update(Request $request, Professor $professor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:professor,email,' . $professor->id,
            'department' => 'nullable|string|max:255',
        ]);

        $professor->update($validated);

        return redirect()->route('professor.index')->with('success', 'Professor updated successfully.');
    }

    // حذف استاد از پایگاه داده
    public function destroy(Professor $professor)
    {
        $professor->delete();
        return redirect()->route('professor.index')->with('success', 'Professor deleted successfully.');
    }

    //نمایش لیست اساتید برای مهمان‌ها (API)
    //public function guestIndex()
//{
    //فقط فیلدهای full_name و username را انتخاب کنید
    //$professors = Professor::all();

    //return response()->json($professors, 200);
//}

    // نمایش جزئیات یک استاد مشخص برای مهمان (API)
    public function guestShow($id)
    {
    // تلاش برای یافتن استاد با شناسه مشخص
    $professor = Professor::find($id);

    // اگر استاد پیدا نشد، خطای 404 بازگردانده شود
    if (!$professor) {
        return response()->json(['error' => 'Professor not found'], 404);
    }

    // بازگرداندن اطلاعات استاد در قالب JSON
    return response()->json($professor, 200);
    }

    public function guestIndex()
{
    $professors = Professor::select('full_name', 'username', 'department', 'average_rating')
        ->with('courses:id,title,faculty_number') // فقط فیلدهای مورد نیاز درس‌ها
        ->orderBy('average_rating', 'desc')
        ->get();

    return response()->json($professors);
}
}