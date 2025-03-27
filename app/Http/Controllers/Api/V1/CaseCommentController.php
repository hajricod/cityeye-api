<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CaseComment;
use App\Models\Cases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $request->validate([
            'comment' => 'required|string|max:1000',
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
     * Display the specified resource.
     */
    public function show(CaseComment $caseComment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CaseComment $caseComment)
    {
        //
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
