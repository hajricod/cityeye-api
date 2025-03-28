<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AuthorizationLevel;
use App\Enums\CaseType;
use App\Events\CaseCreated;
use App\Events\CaseUpdated;
use App\Http\Controllers\Controller;
use App\Models\CasePerson;
use App\Models\Cases;
use App\Models\Evidence;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class CasesController extends Controller
{
    /**
     * GET all cases.
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
                'id' => $case->id,
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
     * POST a new case.
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

        event(new CaseCreated($case));

        return response()->json([
            'message' => 'Case created successfully',
            'case' => $case
        ], 201);
    }

    /**
     * GET specific case details.
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
            'reported_by' => $case->reports->map(function ($report) {
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
     * PUT specific case details.
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

        event(new CaseUpdated($case));

        return response()->json([
            'message' => 'Case updated successfully',
            'case' => $case
        ]);

    }

    /**
     * DELETE a case.
     */
    public function destroy($id)
    {
        $case = Cases::find($id);
        if (!$case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        // Delete the case also delete related data
        $case->delete();

        return response()->json([
            'message' => 'Case deleted successfully.'
        ]);
    }

    /**
     * GET case assignees.
     */
    public function assignees(Cases $case)
    {
        $assignees = $case->assignees()->select('user_id', 'name', 'email', 'role', 'case_assignees.authorization_level', 'case_assignees.created_at', 'case_assignees.updated_at')->get();

        foreach ($assignees as $assignee) {
            unset($assignee['pivot']);
        }

        return response()->json(['assignees' => $assignees], 200);
    }

    /**
     * GET case evidences.
     */
    public function evidences(Cases $case)
    {
        $evidences = $case->evidences()->select('id', 'type', 'description', 'file_path', 'uploaded_by')->get();

        return response()->json(['evidences' => $evidences], 200);
    }

    /**
     * GET case suspects.
     */
    public function suspects(Cases $case)
    {
        $suspects = $case->persons()->where('type', 'suspect')->select('id', 'name', 'age', 'gender', 'role')->get();

        return response()->json(['suspects' => $suspects], 200);
    }

    /**
     * GET case victims.
     */
    public function victims(Cases $case)
    {
        $victims = $case->persons()->where('type', 'victim')->select('id', 'name', 'age', 'gender', 'role')->get();

        return response()->json(['victims' => $victims], 200);
    }

    /**
     * GET case witnesses.
     */
    public function witnesses(Cases $case)
    {
        $witnesses = $case->persons()->where('type', 'witness')->select('id', 'name', 'age', 'gender', 'role')->get();

        return response()->json(['witnesses' => $witnesses], 200);
    }

    protected function truncateDescription(string $text, int $limit = 100): string
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

    /**
     * GET case extracted links.
     */
    public function extractLinks($id)
    {
        $case = Cases::find($id);

        if (!$case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        $textSources = [];

        if ($case->description) {
            $textSources[] = $case->description;
        }

        if ($case->area) {
            $textSources[] = $case->area;
        }

        if ($case->case_name) {
            $textSources[] = $case->case_name;
        }

        // Combine all text from case
        $combinedText = implode(' ', $textSources);

        // Regex to match http(s) + www. links
        preg_match_all('/\b((https?:\/\/)?www\.[^\s]+)\b/i', $combinedText, $matches);

        $links = $matches[1] ?? [];

        return response()->json([
            'case_id' => $case->id,
            'total_links_found' => count($links),
            'links' => array_values(array_unique($links))
        ]);
    }

    /**
     * GET case generated report.
     */
    public function generatePdfReport($id)
    {
        $case = Cases::with('creator')->find($id);
        if (!$case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        $evidence = Evidence::where('case_id', $id)->get();
        $suspects = CasePerson::where('case_id', $id)->where('type', 'suspect')->get();
        $victims = CasePerson::where('case_id', $id)->where('type', 'victim')->get();
        $witnesses = CasePerson::where('case_id', $id)->where('type', 'witness')->get();

        $pdf = Pdf::loadView('pdf.case_report', compact('case', 'evidence', 'suspects', 'victims', 'witnesses'));
        return $pdf->download("case_report_{$case->case_number}.pdf");
    }
}
