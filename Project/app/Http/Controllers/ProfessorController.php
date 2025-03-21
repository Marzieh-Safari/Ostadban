<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    // نمایش لیست اساتید
    public function index()
    {
        $professors = Professor::all();
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

    // نمایش جزئیات یک استاد
    public function show(Professor $professor)
    {
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
}