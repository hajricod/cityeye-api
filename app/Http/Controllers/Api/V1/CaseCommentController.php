<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CaseComment;
use App\Models\Cases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class CaseCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($caseId)
    {
        $case = Cases::findOrFail($caseId);
        $comments = $case->comments()->with('user:id,name')->latest()->get();

        return response()->json($comments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $caseId)
    {
        $user = auth()->user();
        $key = 'comment-rate:' . $user->id;

        // Check if user exceeded rate limit
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'message' => 'Too many comments. Please wait before posting again.'
            ], 429);
        }

        // Allow 5 attempts per 60 seconds
        RateLimiter::hit($key, 60);

        $request->validate([
            'comment' => [
                'required',
                'string',
                'min:5',
                'max:150',
                function ($attribute, $value, $fail) {
                    if (preg_match('/<[^>]*>/', $value)) {
                        $fail('HTML tags are not allowed in comments.');
                    }
                    if (!preg_match('/^[\pL\pN\s.,!?()\-\'";:]+$/u', $value)) {
                        $fail('Comment contains invalid characters. Please use only letters, numbers, and basic punctuation.');
                    }
                },
            ],
        ], [
            'comment.min' => 'Comment must be at least 5 characters long.',
            'comment.max' => 'Comment cannot exceed 150 characters.',
        ]);

        $case = Cases::findOrFail($caseId);

        $comment = new CaseComment();
        $comment->case_id = $case->id;
        $comment->user_id = Auth::id();
        $comment->comment = $request->comment;
        $comment->save();

        return response()->json(['message' => 'Comment added successfully.'], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($caseId, $commentId)
    {
        $comment = CaseComment::where('case_id', $caseId)
            ->where('id', $commentId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully.']);
    }
}
