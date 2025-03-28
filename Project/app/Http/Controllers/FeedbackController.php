<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Professor;
use App\Models\Course;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    // نمایش لیست نظرات
    public function index()
    {
        $feedback = Feedback::all();
        return view('feedback.index', compact('feedback'));
    }

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

    // نمایش لیست نظرات برای مهمان‌ها (API)
    public function guestIndex(Request $request)
    {
    $feedback = Feedback::orderBy('created_at', 'asc')->with(['professor', 'course'])->get(); // مرتب‌سازی بر اساس تاریخ ایجاد و بارگذاری اطلاعات استاد و دوره

    // اگر درخواست از API باشد، داده‌ها را به صورت JSON بازگردانید
    if ($request->expectsJson()) {
        return response()->json($feedback, 200);
    }

    }

    public function rateProfessor(Request $request, Professor $professor) {
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string'
        ]);
        
        $rating = $professor->ratings()->updateOrCreate(
            ['student_id' => $request->user()->id],
            $request->only(['rating', 'comment'])
        );
        
        return response()->json($rating);
    }
}