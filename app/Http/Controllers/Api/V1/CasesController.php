<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AuthorizationLevel;
use App\Enums\CaseType;
use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CasesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'case_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'area' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'case_type' => ['required', Rule::in(array_column(CaseType::cases(), 'value'))],
            'authorization_level' => ['required', Rule::in(array_column(AuthorizationLevel::cases(), 'value'))],
            'report_ids' => 'nullable|array',
            'report_ids.*' => 'exists:reports,id',
        ]);

        $user = Auth::user();

        $case = Cases::create([
            'case_number' => Str::upper(uniqid('CAS-')),
            'case_name' => $validated['case_name'],
            'description' => $validated['description'] ?? null,
            'area' => $validated['area'] ?? null,
            'city' => $validated['city'] ?? null,
            'case_type' => $validated['case_type'],
            'authorization_level' => $validated['authorization_level'],
            'created_by' => $user->id,
        ]);

        // Link reports to the new case
        if (!empty($validated['report_ids'])) {
            Report::whereIn('id', $validated['report_ids'])
                ->update(['case_id' => $case->id]);
        }

        return response()->json([
            'message' => 'Case created successfully',
            'case' => $case
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cases $cases)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cases $case)
    {

        $validated = $request->validate([
            'case_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:500',
            'area' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'case_type' => ['sometimes', Rule::in(array_column(CaseType::cases(), 'value'))],
            'authorization_level' => ['sometimes', Rule::in(array_column(AuthorizationLevel::cases(), 'value'))],
            'report_ids' => 'nullable|array',
            'report_ids.*' => 'exists:reports,id',
        ]);

        if (!empty($validated['report_ids'])) {
            Report::whereIn('id', $validated['report_ids'])
                ->update(['case_id' => $case->id]);
        }

        unset($validated['report_ids']);

        $case->update($validated);

        return response()->json([
            'message' => 'Case updated successfully',
            'case' => $case
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cases $cases)
    {
        //
    }
}
