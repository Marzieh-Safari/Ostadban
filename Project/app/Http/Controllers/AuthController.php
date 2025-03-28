<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use app\Http\Requests\RegisterStudentRequest;
use app\http\Requests\LoginRequest;


class AuthController extends Controller
{

    public function register(RegisterStudentRequest $request) {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
    
        $student = Student::create($validated);
        $token = $student->createToken('student-token')->plainTextToken;

        return response()->json([
            'student' => $student,
            'token' => $token
        ], 201);
    }

    public function login(LoginRequest $request) {
        $student = Student::where('email', $request->email)->first();
    
        if (!$student || !Hash::check($request->password, $student->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    
        return response()->json([
        'token' => $student->createToken('student-token')->plainTextToken
        ]);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

}