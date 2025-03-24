<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CaseStatus;
use App\Enums\CaseType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = Report::orderBy('created_at', 'desc')->get();

        return response()->json(["reports" => $reports]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Report $report)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'civil_id' => 'required|string|max:20',
            'description' => 'required|string|max:1000',
            'city' => 'required|string|max:255',
            'area' => 'nullable|string|max:255',
        ]);

        // Create report entry
        $data = $report->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'civil_id' => $validated['civil_id'],
            'report_id' => Str::upper(uniqid('REP-')),
            'role' => UserRole::Citizen,
            'description' => $validated['description'],
            'city' => $validated['city'],
            'area' => $validated['area'] ?? null,
        ]);

        return response()->json([
            'message' => 'Report submitted successfully',
            'report_id' => $data->report_id,
        ], 201);
    }

    public function status($reportId)
    {
        $report = Report::with('case')->where('report_id', $reportId)->first();

        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        return response()->json([
            'report_id' => $report->report_id,
            'status' => $report->status,
        ]);
    }

    public function getReportStatus($report_id)
    {
        $report = Report::find($report_id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $case = Cases::find($report->case_id);
        if (!$case) {
            return response()->json(['message' => 'Case not yet assigned to this report'], 404);
        }

        return response()->json([
            'report_id' => $report->id,
            'case_number' => $case->case_number,
            'case_name' => $case->case_name,
            'status' => $case->case_status, // e.g., pending, ongoing, closed
            'last_updated' => $case->updated_at->toDateTimeString(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        return response()->json(["report" => $report]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'civil_id' => 'sometimes|string|max:20',
            'description' => 'nullable|string|max:1000',
            'status' => ['sometimes', Rule::in(array_column(CaseStatus::cases(), 'value'))],
        ]);

        $report->update($validated);

        return response()->json([
            'message' => 'Report updated successfully',
            'report' => $report
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
    }
}
