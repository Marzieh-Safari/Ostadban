<?php

// app/Http/Controllers/AdminSystemController.php
namespace App\Http\Controllers;

use App\Models\AdminSystem;
use Illuminate\Http\Request;

class AdminSystemController extends Controller
{
    public function index()
    {
        return AdminSystem::all(); // نمایش تمام ادمین‌ها
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:admin_systems',
            'email' => 'required|email|unique:admin_systems',
            'password' => 'required|string|min:8',
        ]);

        $admin = AdminSystem::create($validated);
        return response()->json($admin, 201); // ایجاد ادمین
    }

    public function show($id)
    {
        $admin = AdminSystem::findOrFail($id);
        return response()->json($admin); // نمایش ادمین خاص
    }

    public function update(Request $request, $id)
    {
        $admin = AdminSystem::findOrFail($id);
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:admin_system,username,' . $id,
            'email' => 'required|email|unique:admin_systems,email,' . $id,
            'password' => 'nullable|string|min:8',
        ]);

        $admin->update($validated);
        return response()->json($admin); // به‌روزرسانی ادمین
    }

    public function destroy($id)
    {
        $admin = AdminSystem::findOrFail($id);
        $admin->delete();
        return response()->json(['message' => 'Admin deleted']); // حذف ادمین
    }
}