<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\Evidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        $response = [
            'id' => $evidence->id,
            'case_id' => $evidence->case_id,
            'type' => $evidence->type,
            'description' => $evidence->description,
            'remarks' => $evidence->remarks,
            'uploaded_by' => $evidence->uploaded_by,
            'created_at' => $evidence->created_at->toDateTimeString(),
            'file_path' => $evidence->file_path,
        ];

        if ($evidence->type === 'image' && $evidence->file_path && Storage::disk('public')->exists($evidence->file_path)) {
            $sizeInBytes = Storage::disk('public')->size($evidence->file_path);
            $sizeFormatted = $this->formatFileSize($sizeInBytes);
            $response['image_size'] = $sizeFormatted;
        }

        return response()->json(['evidence' => $response]);
    }

    private function formatFileSize($bytes)
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
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

    public function download(Evidence $evidence)
    {
        if ($evidence->type !== 'image' || !$evidence->file_path) {
            return response()->json(['message' => 'No image available for this evidence.'], 404);
        }

        if (!Storage::disk('public')->exists($evidence->file_path)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        return response()->file(storage_path('app/public/' . $evidence->file_path));
    }
}
