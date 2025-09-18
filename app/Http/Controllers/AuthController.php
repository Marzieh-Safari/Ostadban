<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Exceptions\JWTException;
//use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AuthController extends Controller
{
    public function register(Request $request)
{
    try {
        $validated = $request->validate([
            'email' => [
                'required',
                'regex:/^[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z]{1,}$/',
                Rule::unique('users', 'email')
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols()
            ],
        ], [
            'email.required' => 'لطفا ایمیل خود را وارد کنید',
            'email.email' => 'فرمت ایمیل وارد شده معتبر نیست',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است. لطفا وارد شوید.',
            'password.required' => 'لطفا رمز عبور را وارد کنید',
            'password.confirmed' => 'رمز عبور و تکرار آن مطابقت ندارند.',
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student'
        ]);

        $token = JWTAuth::fromUser($user);
        
        return $this->respondWithToken($token, 'ثبت‌نام با موفقیت انجام شد', $user);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'این ایمیل قبلاً ثبت شده است. لطفا وارد شوید',
            'errors' => $e->errors()
        ], 422);
    }
}

    public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ], [
        'email.required' => 'لطفا ایمیل خود را وارد کنید',
        'email.email' => 'فرمت ایمیل وارد شده معتبر نیست',
        'password.required' => 'لطفا رمز عبور را وارد کنید'
    ]);

    if (!$token = JWTAuth::attempt($credentials)) {
        $userExists = User::where('email', $request->email)->exists();
        
        if ($userExists) {
            return response()->json([
                'success' => false,
                'message' => 'رمز عبور وارد شده نادرست است'
            ], 401);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'کاربری با این ایمیل یافت نشد. لطفا ابتدا ثبت‌نام کنید'
        ], 404);
    }

    $user = JWTAuth::user();
    
    return $this->respondWithToken($token, 'ورود موفقیت‌آمیز بود', $user);
}

protected function respondWithToken($token, $message = null, $user)
{
    return response()->json([
        'success' => true,
        'message' => $message,
        'data' => [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (int) JWTAuth::factory()->getTTL() * 7776000,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]
        ]
    ]);
}

// ...

/**
 * خروج کاربر از سیستم
 * 
 * @header Authorization Bearer {token}
 * 
 * @response {
 *   "success": true,
 *   "message": "خروج با موفقیت انجام شد"
 * }
 * @response 401 {
 *   "success": false,
 *   "message": "توکن احراز هویت ارائه نشده است"
 * }
 * @response 500 {
 *   "success": false,
 *   "message": "خطا در عملیات خروج"
 * }
 */
public function logout(Request $request)
{
    try {
        
        if (!$token = $request->bearerToken()) {
            return response()->json([
                'success' => false,
                'message' => 'توکن احراز هویت ارائه نشده است'
            ], 401);
        }

        
        JWTAuth::setToken($token);

        
        JWTAuth::invalidate();

        return response()->json([
            'success' => true,
            'message' => 'خروج با موفقیت انجام شد'
        ]);

    } catch (TokenExpiredException $e) {
        return response()->json([
            'success' => false,
            'message' => 'توکن منقضی شده است'
        ], 401);
    } catch (JWTException $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطا در عملیات خروج'
        ], 500);
    }
}

// ...

/**
 * دریافت اطلاعات کاربر احراز هویت شده
 * 
 * @header Authorization Bearer {token}
 * 
 * @response {
 *   "success": true,
 *   "data": {
 *     "id": 1,
 *     "email": "user@example.com",
 *     "username": "user",
 *     "full_name": "کاربر نمونه",
 *     "major": "مهندسی نرم‌افزار",
 *     "role": "student"
 *   }
 * }
 * @response 401 {
 *   "success": false,
 *   "message": "توکن احراز هویت ارائه نشده است"
 * }
 * @response 401 {
 *   "success": false,
 *   "message": "کاربر احراز هویت نشده"
 * }
 */
public function user(Request $request)
{
    try {
        
        if (!$token = $request->bearerToken()) {
            return response()->json([
                'success' => false,
                'message' => 'توکن احراز هویت ارائه نشده است'
            ], 401);
        }

        
        $user = JWTAuth::parseToken()->authenticate();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر احراز هویت نشده'
            ], 401);
        }

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'username' => $user->username,
            'full_name' => $user->full_name,
            'major' => $user->major,
            'role' => $user->role,
        ]);

    } catch (JWTException $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطا در دریافت اطلاعات کاربر'
        ], 500);
    }
}

}