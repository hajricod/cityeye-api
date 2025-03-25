<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CasePersonType;
use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\Cases;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CasePersonsController extends Controller
{

    protected function validateType($type)
    {
        if (!in_array($type, array_column(CasePersonType::cases(), 'value'))) {
            abort(400, 'Invalid person type');
        }
    }

    public function allPersons(Cases $case)
    {
        return response()->json($case->persons()->get());
    }

    public function index(Cases $case, $type)
    {
        $this->validateType($type);
        return response()->json($case->persons()->where('type', $type)->get());
    }

    public function store(Request $request, Cases $case, $type)
    {
        $this->validateType($type);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
            'gender' => 'required|string|in:Male,Female,Other',
            'role' => 'nullable|string|max:255',
        ]);

        $person = $case->persons()->create(array_merge($validated, ['type' => $type]));

        return response()->json($person, 201);
    }

    public function show(Cases $case, $type, $id)
    {
        $this->validateType($type);

        $person = $case->persons()->where('type', $type)->findOrFail($id);

        return response()->json($person);
    }

    public function update(Request $request, Cases $case, $type, $id)
    {
        $this->validateType($type);

        $person = $case->persons()->where('type', $type)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'age' => 'sometimes|required|integer|min:0',
            'gender' => ['sometimes', Rule::in(array_column(Gender::cases(), 'value'))],
            'role' => 'nullable|string|max:255',
        ]);

        $person->update($validated);

        return response()->json([
            'message' => ucfirst($type) . ' updated successfully.',
            'data' => $person
        ]);
    }

    public function destroy(Cases $case, $type, $id)
    {
        $this->validateType($type);

        $person = $case->persons()->where('type', $type)->findOrFail($id);
        $person->delete();

        return response()->json(['message' => ucfirst($type) . ' deleted.']);
    }
}
