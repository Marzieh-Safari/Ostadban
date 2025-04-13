<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Professor;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\User;

class FeedbackController extends Controller
{

    // ذخیره نظر جدید در پایگاه داده
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:student,id',
            'faculty_number' => 'required|exists:professor,id',
            'course_id' => 'required|exists:course,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        Feedback::create($validated);

        return redirect()->route('feedback.index')->with('success', 'Feedback created successfully.');
    } 

    //نمایش لیست نظرات (API)
    public function Index(Request $request)
    {
    $feedbacks = Feedback::orderBy('created_at', 'asc')->with(['professor', 'course'])->get(); // مرتب‌سازی بر اساس تاریخ ایجاد و بارگذاری اطلاعات استاد و دوره

    // اگر درخواست از API باشد، داده‌ها را به صورت JSON بازگردانید
    if ($request->expectsJson()) {
        return response()->json($feedbacks, 200);
    }

    }

    public function rateProfessor(Request $request, User $professor)
    {
        // بررسی اینکه این کاربر واقعاً یک استاد است
        if ($professor->type !== 'professor') {
            return response()->json(['error' => 'This user is not a professor.'], 400);
        }
    
        // اعتبارسنجی درخواست
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string'
        ]);
    
        // ایجاد یا بروزرسانی امتیاز برای استاد
        $rating = $professor->ratings()->updateOrCreate(
            ['student_id' => $request->user()->id], // فرض اینکه دانشجو به سیستم لاگین کرده است
            $request->only(['rating', 'comment'])
        );
    
        return response()->json($rating, 200);
    }
}