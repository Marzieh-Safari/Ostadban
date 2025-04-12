<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // نمایش تمام کاربران براساس نوع (admin, professor, student)
    public function index(Request $request)
    {
        $type = $request->query('type'); // گرفتن نوع کاربر از کوئری
        $users = User::where('type', $type)->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    // ایجاد کاربر جدید (admin, professor, student)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:admin,professor,student',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

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
    }

    // نمایش اطلاعات یک کاربر خاص براساس ID
    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    // به‌روزرسانی اطلاعات کاربر
    public function update(Request $request, $id)
    {
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
    }

    // حذف کاربر
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'کاربر با موفقیت حذف شد',
        ]);
    }

    // جستجوی کاربران براساس نوع و نام
    public function search(Request $request)
    {
        $type = $request->query('type'); // نوع کاربر (admin, professor, student)
        $name = $request->query('name'); // نام کاربر

        $users = User::where('type', $type)
            ->where('username', 'LIKE', '%' . $name . '%')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    // عملیات خاص مرتبط با دانشجویان (تأیید یا رد کردن دانشجو)
    public function approveStudent($id)
    {
        $student = User::where('type', 'student')->findOrFail($id);
        $student->update(['is_approved' => true]);

        return response()->json([
            'success' => true,
            'message' => 'دانشجو تأیید شد',
        ]);
    }

    public function disapproveStudent($id)
    {
        $student = User::where('type', 'student')->findOrFail($id);
        $student->update(['is_approved' => false]);

        return response()->json([
            'success' => true,
            'message' => 'دانشجو رد شد',
        ]);
    }

    // نمایش محبوب‌ترین اساتید براساس تعداد جستجو
    public function mostSearchedProfessors()
    {
        $professors = User::where('type', 'professor')
            ->orderByDesc('search_count')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $professors,
        ]);
    }
}