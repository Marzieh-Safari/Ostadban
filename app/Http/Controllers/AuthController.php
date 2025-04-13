<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\RegisterStudentRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentVerificationMail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterStudentRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $validated['verification_token'] = Str::random(60);
        $validated['token_expires_at'] = now()->addHours(24);
        $validated['type'] = 'student'; // مشخص کردن نوع کاربر به عنوان دانشجو
        
        $student = User::create($validated); // ایجاد رکورد جدید در جدول users

        // ارسال ایمیل تأیید
        Mail::to($student->email)->send(new StudentVerificationMail($student));
        
        return response()->json([
            'student' => $student
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $student = User::where('email', $request->email)->where('type', 'student')->first(); // بررسی نوع کاربر

        if (!$student || !Hash::check($request->password, $student->password)) {
            return response()->json(['message' => 'اعتبارسنجی ناموفق'], 401);
        }

        if (!$student->email_verified_at) {
            return response()->json(['message' => 'لطفاً ابتدا ایمیل خود را تأیید کنید.'], 403);
        }

        if (!$student->is_approved) {
            return response()->json(['message' => 'حساب شما هنوز توسط مدیریت تأیید نشده است.'], 403);
        }

        $token = $student->createToken('student-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'student' => $student
        ]);
    }

    public function verifyEmail($token)
    {
        // یافتن دانشجو با توکن معتبر و بررسی انقضا
        $student = User::where('verification_token', $token)
            ->where('type', 'student') // فقط دانشجویان
            ->where('token_expires_at', '>', now())
            ->first();

        if (!$student) {
            return response()->json([
                'message' => 'توکن نامعتبر یا منقضی شده است. لطفاً درخواست توکن جدید دهید.',
                'action_required' => 'request_new_token'
            ], 410); // کد 410 = Gone (مناسب برای منابع منقضی شده)
        }

        // بررسی اینکه ایمیل قبلاً تأیید نشده باشد
        if ($student->email_verified_at) {
            return response()->json([
                'message' => 'این ایمیل قبلاً تأیید شده است.',
                'status' => 'already_verified'
            ], 208); // کد 208 = Already Reported
        }

        // بروزرسانی وضعیت دانشجو
        $student->update([
            'email_verified_at' => now(),
            'verification_token' => null,
            'token_expires_at' => null,
        ]);

        // پاسخ موفقیت‌آمیز با اطلاعات تکمیلی
        return response()->json([
            'message' => 'ایمیل شما با موفقیت تأیید شد!',
            'verified_at' => $student->fresh()->email_verified_at,
            'next_steps' => [
                'login' => '/api/login',
                'profile' => '/api/profile'
            ]
        ]);
    }

    public function resendVerificationEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $student = User::where('email', $request->email)->where('type', 'student')->first();

        if ($student->email_verified_at) {
            return response()->json(['message' => 'ایمیل قبلاً تأیید شده است.'], 208);
        }

        $student->update([
            'verification_token' => Str::random(60),
            'token_expires_at' => now()->addHours(24)
        ]);

        Mail::to($student->email)->send(new StudentVerificationMail($student));

        return response()->json([
            'message' => 'ایمیل تأیید جدید ارسال شد.',
            'expires_at' => $student->token_expires_at
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'خروج موفقیت‌آمیز بود.']);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }
}