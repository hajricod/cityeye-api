<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CaseStatus;
use App\Http\Controllers\Controller;
use App\Models\Cases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OfficerCasesController extends Controller
{
    public function index()
    {
        $officer = Auth::user();

        $cases = Cases::whereHas('assignees', function ($query) use ($officer) {
            $query->where('user_id', $officer->id);
        })
        // ->with(['assignee', 'createdBy'])
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'officer_id' => $officer->id,
            'total_assigned_cases' => $cases->count(),
            'cases' => $cases
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $officer = Auth::user();

        // Only allow valid status enum values
        $validated = $request->validate([
            'case_status' => ['required', Rule::in(array_column(CaseStatus::cases(), 'value'))],
        ]);

        $case = Cases::whereHas('assignee', function ($query) use ($officer) {
            $query->where('user_id', $officer->id);
        })->findOrFail($id);

        $case->case_status = $validated['case_status'];
        $case->save();

        return response()->json([
            'message' => 'Case status updated successfully.',
            'case_id' => $case->id,
            'case_status' => $case->case_status
        ]);
    }
}
