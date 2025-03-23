<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\Evidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvidenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Cases $case)
    {
        $validated = $request->validate([
            'type' => 'required|in:text,image',
            'description' => 'required_if:type,text|string|max:1000',
            'image' => 'required_if:type,image|image|mimes:jpeg,png,jpg,gif|max:5120', // max 5MB
            'remarks' => 'nullable|string|max:1000',
        ]);

        $data = [
            'case_id' => $case->id,
            'type' => $validated['type'],
            'uploaded_by' => Auth::id(),
            'remarks' => $validated['remarks'] ?? null,
        ];

        if ($validated['type'] === 'text') {
            $data['description'] = $validated['description'];
        } elseif ($request->hasFile('image')) {
            // Store image and save path
            $path = $request->file('image')->store('evidences', 'public');
            $data['file_path'] = $path;
        }

        $evidence = Evidence::create($data);

        return response()->json([
            'message' => 'Evidence recorded successfully',
            'evidence' => $evidence
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Evidence $evidence)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Evidence $evidence)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Evidence $evidence)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Evidence $evidence)
    {
        //
    }
}
