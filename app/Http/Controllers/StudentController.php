<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('admin')->only(['approve', 'disapprove']);
    }

    public function index()
    {
        return Student::where('type', 'student')
            ->where('is_approved', true)
            ->get();
    }

    public function show($id)
    {
        return Student::where('type', 'student')
            ->findOrFail($id);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $student = $request->user();
        $data = $request->validated();
        
        // جلوگیری از تغییر type
        if (isset($data['type'])) {
            unset($data['type']);
        }
        
        $student->update($data);
        
        return response()->json([
            'success' => true,
            'data' => $student,
            'message' => 'پروفایل با موفقیت بروزرسانی شد'
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed'
        ]);
        
        $student = $request->user();
        
        if (!Hash::check($request->current_password, $student->password)) {
            return response()->json([
                'success' => false,
                'message' => 'رمز عبور فعلی نادرست است'
            ], 400);
        }
        
        $student->update(['password' => Hash::make($request->new_password)]);
        
        return response()->json([
            'success' => true,
            'message' => 'رمز عبور با موفقیت تغییر یافت'
        ]);
    }

    public function approve($id)
    {
        $student = Student::where('type', 'student')
            ->findOrFail($id);
            
        $student->update(['is_approved' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'دانشجو تأیید شد'
        ]);
    }

    public function disapprove($id)
    {
        $student = Student::where('type', 'student')
            ->findOrFail($id);
            
        $student->update(['is_approved' => false]);
        
        return response()->json([
            'success' => true,
            'message' => 'دانشجو رد شد'
        ]);
    }
}