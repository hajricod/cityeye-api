<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
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
            'uploaded_by' => $evidence->uploader->name ?? 'N/A',
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

    public function getImage(Evidence $evidence)
    {
        // Check if evidence is of type 'image'
        if ($evidence->type !== 'image') {
            return response()->json(['message' => 'This evidence is not an image.'], 400);
        }

        // Check if image exists
        if (!$evidence->file_path || !Storage::disk('public')->exists($evidence->file_path)) {
            return response()->json(['message' => 'Image file not found.'], 404);
        }

        // Return the image file
        $filePath = storage_path('app/public/' . $evidence->file_path);
        $mimeType = mime_content_type($filePath);

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
        ]);
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
        // Detect evidence type
        $type = $evidence->type;

        // Validate based on type
        if ($type === 'text') {
            $validated = $request->validate([
                'description' => 'required|string|max:1000',
                'remarks' => 'nullable|string|max:1000',
            ]);

            $evidence->description = $validated['description'];
            $evidence->remarks = $validated['remarks'] ?? $evidence->remarks;

        } elseif ($type === 'image') {
            $validated = $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
                'remarks' => 'nullable|string|max:1000',
            ]);

            // Delete old image if it exists
            if ($evidence->file_path && Storage::disk('public')->exists($evidence->file_path)) {
                Storage::disk('public')->delete($evidence->file_path);
            }

            // Store new image
            $newPath = $request->file('image')->store('evidences', 'public');
            $evidence->file_path = $newPath;
            $evidence->remarks = $validated['remarks'] ?? $evidence->remarks;
        } else {
            return response()->json(['message' => 'Invalid evidence type.'], 400);
        }

        $evidence->save();

        return response()->json([
            'message' => 'Evidence updated successfully',
            'evidence' => $evidence
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Evidence $evidence)
    {
        $user = Auth::user();

        $evidence->delete(); // Soft delete

        // Insert audit log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'deleted_evidence',
            'description' => "Evidence ID {$evidence->id} soft-deleted by {$user->name} ({$user->role->value})"
        ]);

        return response()->json(['message' => 'Evidence soft-deleted successfully']);
    }

    public function confirmDelete($id)
    {
        $evidence = Evidence::withTrashed()->findOrFail($id);

        return response()->json([
            'message' => "Are you sure you want to permanently delete Evidence ID: {$evidence->id}? (yes/no)"
        ]);
    }

    public function hardDelete(Request $request, $id)
    {
        $evidence = Evidence::withTrashed()->findOrFail($id);

        $user = Auth::user();

        // Check 'confirm' input
        $confirm = strtolower($request->input('confirm'));

        if ($confirm !== 'yes') {
            return response()->json([
                'message' => "Deletion canceled. Send 'confirm: yes' to permanently delete Evidence ID: {$evidence->id}."
            ], 400);
        }

        // Delete associated file if it's an image
        if ($evidence->type === 'image' && $evidence->file_path && Storage::disk('public')->exists($evidence->file_path)) {
            Storage::disk('public')->delete($evidence->file_path);
        }

        // Log the hard delete action
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'hard_deleted_evidence',
            'description' => "Evidence ID {$evidence->id} permanently deleted by {$user->name} ({$user->role->value})"
        ]);

        // Permanently delete the evidence "Hard delete"
        $evidence->forceDelete();

        return response()->json(['message' => "Evidence ID {$evidence->id} permanently deleted."]);
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
