<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
//use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email|unique:users,email',
        'password' => [
            'required',
            'confirmed',
            Password::min(8)->mixedCase()->numbers()->symbols()
        ],
        'username' => 'required|string|unique:users,username|max:20',
        'full_name' => 'required|string|max:50',
        'major' => 'required|string|max:50',
    ]);

    $user = User::create([
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'username' => $validated['username'],
        'full_name' => $validated['full_name'],
        'major' => $validated['major'],
        'role' => 'student'
    ]);

    
    $token = JWTAuth::fromUser($user);
    return $this->respondWithToken($token, 'ثبت‌نام با موفقیت انجام شد');
}

    public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if (!$token = JWTAuth::attempt($credentials)) {
        return response()->json([
            'success' => false,
            'message' => 'ایمیل یا رمز عبور نادرست'
        ], 401);
    }

    return $this->respondWithToken($token);
}


protected function respondWithToken($token, $message = null)
{
    return response()->json([
        'success' => true,
        'message' => $message,
        'data' => [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => JWTAuth::user()->only([
                'id',
                'username',
                'email',
                'full_name',
                'major',
                'role'
            ])
        ]
    ]);
}
}