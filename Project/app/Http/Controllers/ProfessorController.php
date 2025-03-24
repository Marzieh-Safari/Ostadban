<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    // نمایش لیست اساتید (برای وب و API)
    public function index(Request $request)
    {
        // جست‌وجو و مرتب‌سازی
        $query = Professor::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('sort')) {
            $query->orderBy($request->sort, $request->get('direction', 'asc'));
        }

        $professors = $query->get();

        // اگر درخواست از API باشد، داده‌ها را به صورت JSON بازگردانید
        if ($request->expectsJson()) {
            return response()->json($professors, 200);
        }

        // اگر درخواست از وب باشد، ویو را بازگردانید
        return view('professors.index', compact('professors'));
    }

    // نمایش فرم ایجاد استاد جدید
    public function create()
    {
        return view('professors.create');
    }

    // ذخیره استاد جدید در پایگاه داده
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:professors',
            'department' => 'nullable|string|max:255',
        ]);

        Professor::create($validated);

        return redirect()->route('professors.index')->with('success', 'Professor created successfully.');
    }

    // نمایش جزئیات یک استاد (برای وب و API)
    public function show(Professor $professor, Request $request)
    {
        // اگر درخواست از API باشد، داده‌ها را به صورت JSON بازگردانید
        if ($request->expectsJson()) {
            return response()->json($professor, 200);
        }

        // اگر درخواست از وب باشد، ویو را بازگردانید
        return view('professors.show', compact('professor'));
    }

    // نمایش فرم ویرایش استاد
    public function edit(Professor $professor)
    {
        return view('professors.edit', compact('professor'));
    }

    // به‌روزرسانی اطلاعات استاد
    public function update(Request $request, Professor $professor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:professors,email,' . $professor->id,
            'department' => 'nullable|string|max:255',
        ]);

        $professor->update($validated);

        return redirect()->route('professors.index')->with('success', 'Professor updated successfully.');
    }

    // حذف استاد از پایگاه داده
    public function destroy(Professor $professor)
    {
        $professor->delete();
        return redirect()->route('professors.index')->with('success', 'Professor deleted successfully.');
    }

    // نمایش لیست اساتید برای مهمان‌ها (API)
    public function guestIndex()
    {
        // فقط لیست اساتید را بازگردانید
        $professors = Professor::all();
        return response()->json($professors, 200);
    }
}