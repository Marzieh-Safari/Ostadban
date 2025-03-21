<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // نمایش لیست کاربران
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    // نمایش فرم ایجاد کاربر جدید
    public function create()
    {
        return view('users.create');
    }

    // ذخیره کاربر جدید در پایگاه داده
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    // نمایش جزئیات یک کاربر
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    // نمایش فرم ویرایش کاربر
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    // به‌روزرسانی اطلاعات کاربر
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    // حذف کاربر از پایگاه داده
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}