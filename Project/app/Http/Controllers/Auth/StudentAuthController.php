<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\StudentVerificationMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
class StudentAuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:students',
            'password' => 'required|min:8',
        ]);

        $student = Student::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'verification_token' => Student::generateVerificationToken(),
            'token_expires_at' => Carbon::now()->addHours(24),
            'is_approved' => false
        ]);

        // ارسال ایمیل احراز
        Mail::to($student->email)->send(new StudentVerificationMail($student));

        return response()->json([
            'message' => 'Registration successful. Please check your email for verification.'
        ], 201);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'token' => 'required'
        ]);

        $student = Student::where('verification_token', $request->token)
            ->where('token_expires_at', '>', Carbon::now())
            ->first();

        if (!$student) {
            return response()->json([
                'message' => 'Invalid or expired token.'
            ], 400);
        }

        $student->update([
            'is_approved' => true,
            'verification_token' => null,
            'token_expires_at' => null
        ]);

        return response()->json([
            'message' => 'Email verified successfully. You can now login.'
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $student = Student::where('email', $request->email)->first();

        if (!$student || !Hash::check($request->password, $student->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$student->is_approved) {
            return response()->json([
                'message' => 'Please verify your email first.'
            ], 403);
        }

        $token = $student->createToken('student_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'student' => $student
        ]);
    }
}