<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CasePerson;
use Illuminate\Http\Request;

class CasePersonsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $case_persons = CasePerson::where('case_id', $id)->get();

        if (count($case_persons) == 0) {
            return response()->json(['message' => 'Case persons not found'], 404);
        }

        return response()->json(["persons" => $case_persons]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($case_id, $person_id)
    {
        $case_person = CasePerson::where(['case_id' => $case_id, "id" => $person_id])->get();

        if (count($case_person) == 0) {
            return response()->json(['message' => 'Case person not found'], 404);
        }

        return response()->json($case_person);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CasePerson $casePerson)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CasePerson $casePerson)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($case_id, $person_id)
    {
        $case_person = CasePerson::where(['case_id' => $case_id, "id" => $person_id])->first();

        if (count($case_person) == 0) {
            return response()->json(['message' => 'Case person not found'], 404);
        }

        $case_person->delete();

        return response()->json(['message' => 'Case person deleted successfully']);
    }
}
