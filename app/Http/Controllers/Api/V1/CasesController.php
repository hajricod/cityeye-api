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
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Cases::with('creator:id,name')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('case_name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        $cases = $query->get();

        $formattedCases = $cases->map(function ($case) {
            return [
                'case_number' => $case->case_number,
                'case_name' => $case->case_name,
                'description' => $this->truncateDescription($case->description),
                'area' => $case->area,
                'city' => $case->city,
                'created_by' => $case->creator->name ?? 'N/A',
                'created_at' => $case->created_at->toDateTimeString(),
                'case_type' => $case->case_type,
                'authorization_level' => $case->authorization_level,
            ];
        });


        return response()->json($formattedCases,200);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'case_name' => 'required|string|max:255',
            'description' => 'nullable|string',
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
    public function show(Cases $case)
    {
        $case->load([
            'creator:id,name,email',
            'reports:id,name,email,civil_id,role,case_id',
            'assignees:id,name,role',
            'persons:id,case_id,type'
        ]);

        $case->loadCount([
            'assignees as assignee_count',
            'evidences as evidence_count',
            'persons as suspect_count' => function ($q) {
                $q->where('type', 'suspect');
            },
            'persons as victim_count' => function ($q) {
                $q->where('type', 'victim');
            },
            'persons as witness_count' => function ($q) {
                $q->where('type', 'witness');
            },
        ]);

        $response = [
            'case_number' => $case->case_number,
            'case_name' => $case->case_name,
            'description' => $case->description,
            'area' => $case->area,
            'city' => $case->city,
            'created_by' => $case->creator->name ?? 'N/A',
            'created_at' => $case->created_at->toDateTimeString(),
            'case_type' => $case->case_type,
            'case_level' => $case->authorization_level, // assuming case_level = authorization_level
            'authorization_level' => $case->reports->map(function ($report) {
                return [
                    'report_id' => $report->id,
                    'name' => $report->name,
                    'email' => $report->email,
                    'civil_id' => $report->civil_id,
                    'role' => $report->role,
                ];
            }),
            'number_of_assignees' => $case->assignee_count,
            'number_of_evidences' => $case->evidence_count,
            'number_of_suspects' => $case->suspect_count,
            'number_of_victims' => $case->victim_count,
            'number_of_witnesses' => $case->witness_count,
        ];

        return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cases $case)
    {

        $validated = $request->validate([
            'case_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
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

    function truncateDescription(string $text, int $limit = 100): string
    {
        if (strlen($text) <= $limit) {
            return $text;
        }

        // Truncate safely without cutting a word
        $truncated = substr($text, 0, $limit);

        // Remove partial word at the end
        if (substr($text, $limit, 1) !== ' ') {
            $truncated = preg_replace('/\s+\S*$/', '', $truncated);
        }

        return $truncated . ' ...';
    }
}
