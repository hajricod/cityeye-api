<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CasePersonType;
use App\Enums\CaseRole;
use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\Cases;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CasePersonsController extends Controller
{

    protected function validateType($type)
    {
        if (!in_array($type->value, array_column(CasePersonType::cases(), 'value'))) {
            abort(400, 'Invalid person type');
        }
    }

    /**
     * GET all persons related to a case.
     */
    public function allPersons(Cases $case)
    {
        return response()->json($case->persons()->get());
    }

    /**
     * GET persons related to a case by type.
     */
    public function index(Cases $case, CasePersonType $type)
    {
        $this->validateType($type);
        return response()->json($case->persons()->where('type', $type)->get());
    }

    /**
     * POST a person related to a case by type.
     */
    public function store(Request $request, Cases $case, CasePersonType $type)
    {
        $this->validateType($type);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
            'gender' => ['required', Rule::in(array_column(Gender::cases(), 'value'))],
            'role' => ['sometimes', Rule::in(array_column(CaseRole::cases(), 'value'))],
        ]);

        $person = $case->persons()->create(array_merge($validated, ['type' => $type]));

        return response()->json([
            'message' => ucfirst($type->value) . ' added successfully',
            'data' => $person
        ], 201);
    }

    /**
     * GET a person related to a case by type.
     */
    public function show(Cases $case, CasePersonType $type, $id)
    {
        $this->validateType($type);

        $person = $case->persons()->where('type', $type)->findOrFail($id);

        return response()->json($person);
    }

    /**
     * PUT a person related to a case by type.
     */
    public function update(Request $request, Cases $case, CasePersonType $type, $id)
    {
        $this->validateType($type);

        $person = $case->persons()->where('type', $type)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'age' => 'sometimes|required|integer|min:0',
            'gender' => ['sometimes', Rule::in(array_column(Gender::cases(), 'value'))],
            'role' => ['sometimes', Rule::in(array_column(CaseRole::cases(), 'value'))],
        ]);

        $person->update($validated);

        return response()->json([
            'message' => ucfirst($type->value) . ' updated successfully.',
            'data' => $person
        ]);
    }

    /**
     * DELETE a person related to a case by type.
     */
    public function destroy(Cases $case, CasePersonType $type, $id)
    {
        $this->validateType($type);

        $person = $case->persons()->where('type', $type)->findOrFail($id);
        $person->delete();

        return response()->json(['message' => ucfirst($type->value) . ' deleted successfully.']);
    }
}
