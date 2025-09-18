<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FeedbackReportController extends Controller
{
    public function reportFeedback(Request $request)
    {

        if (!$token = $request->bearerToken()) {
        return response()->json([
            'success' => false,
            'message' => 'توکن احراز هویت ارائه نشده است'
        ], 401);
    }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|in:abuse,irrelevant,spam,misleading,other',
            'details' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }


        $report = DB::table('feedback_reports')->insert([
            'feedback_id' => $request->input('feedback_id'), 
            'reason' => $request->input('reason'),
            'details' => $request->input('details'),
            'user_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        return response()->json(['message' => 'Feedback reported successfully'], 200);
    }
}
