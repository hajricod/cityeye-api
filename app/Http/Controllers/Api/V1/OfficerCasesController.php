<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
