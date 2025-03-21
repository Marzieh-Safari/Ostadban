<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\User;
use App\Models\Professor;
use App\Models\Course;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    // نمایش لیست نظرات
    public function index()
    {
        $feedbacks = Feedback::all();
        return view('feedbacks.index', compact('feedbacks'));
    }

    // ذخیره نظر جدید در پایگاه داده
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'professor_id' => 'required|exists:professors,id',
            'course_id' => 'required|exists:courses,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        Feedback::create($validated);

        return redirect()->route('feedbacks.index')->with('success', 'Feedback created successfully.');
    }
}