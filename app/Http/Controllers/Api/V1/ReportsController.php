<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportsController extends Controller
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

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
    }
}
