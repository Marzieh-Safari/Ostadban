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
        return Student::where('is_approved', true)->get();
    }

    public function show($id)
    {
        return Student::findOrFail($id);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $student = $request->user();
        $student->update($request->validated());
        
        return response()->json($student);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed'
        ]);
        
        $student = $request->user();
        
        if (!Hash::check($request->current_password, $student->password)) {
            return response()->json(['message' => 'رمز عبور فعلی نادرست است'], 400);
        }
        
        $student->update(['password' => Hash::make($request->new_password)]);
        
        return response()->json(['message' => 'رمز عبور با موفقیت تغییر یافت']);
    }

    public function approve($id)
    {
        $student = Student::findOrFail($id);
        $student->update(['is_approved' => true]);
        
        return response()->json(['message' => 'دانشجو تأیید شد']);
    }

    public function disapprove($id)
    {
        $student = Student::findOrFail($id);
        $student->update(['is_approved' => false]);
        
        return response()->json(['message' => 'دانشجو رد شد']);
    }
}