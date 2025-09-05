<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User;

class SearchController extends Controller
{
    public function search(Request $request)
{
    $query = $request->input('query');

    if (empty($query) || !is_string($query) || preg_match('/[a-zA-Z]/', $query)) {
        return response()->json([
            'success' => false,
            'message' => 'جستجو باید به فارسی و غیر خالی باشد'
        ], 400);
    }

    $courses = Course::where('title', 'like', "%{$query}%")
        ->orWhere('slug', 'like', "%{$query}%")
        ->select('title', 'slug', 'department')
        ->limit(5)
        ->get()
        ->map(function($course) {
            return [
                'type' => 'course',
                'title' => $course->title,
                'slug' => $course->slug,
                'department' => $course->department
            ];
        });

    $professors = User::where('role', 'professor')
        ->where(function($q) use ($query) {
            $q->where('full_name', 'like', "%{$query}%")
              ->orWhere('username', 'like', "%{$query}%");
        })
        ->select('full_name', 'username', 'department', 'is_board_member')
        ->limit(5)
        ->get()
        ->map(function($professor) {
            $result = [
                'type' => 'professor',
                'full_name' => $professor->full_name,
                'username' => $professor->username,
                'department' => $professor->department
            ];

            if ($professor->is_board_member) {
                $result['is_board_member'] = true;
            }

            return $result;
        });

    $results = $courses->concat($professors)
        ->shuffle()
        ->take(5);

    return response()->json([
        'success' => true,
        'data' => $results
    ]);
}
}