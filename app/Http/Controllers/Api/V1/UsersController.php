<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AuthorizationLevel;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    /**
     * GET all users
     */
    public function index(User $user)
    {
        $users = $user::all();
        return response()->json(["users" => $users]);
    }

    /**
     * POST new user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    /**
     * GET a specific user
     */
    public function show(User $user)
    {
        return response()->json($user, 200);
    }

    /**
     * PUT a specific user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ], 200);
    }

    /**
     * PUT user role.
     */
    public function updateUserRole(Request $request, $id): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            'role' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))]
        ]);

        // Find the user
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Update role
        $user->role = $validated['role'];
        $user->save();

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user
        ], 200);
    }

    /**
     * PUT user authorization level.
     */
    public function updateUserAuthLevel(Request $request, $id): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            'authorization_level' => ['required', Rule::in(array_column(AuthorizationLevel::cases(), 'value'))]
        ]);

        // Find the user
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($user->role !== UserRole::Officer) {
            return response()->json([
                'message' => 'User is not an officer'
            ], 403);
        }

        // Update authorization_level
        $user->authorization_level = $validated['authorization_level'];
        $user->save();

        return response()->json([
            'message' => 'User authorization level updated successfully',
            'user' => $user
        ], 200);
    }

    /**
     * DELETE a specific user.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }

    /**
     * POST alert to all users.
     */
    public function sendAlert(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:100',
            'message' => 'required|string|max:1000',
        ]);

        (new EmailNotificationService)->sendSafetyAlert(
            $request->subject,
            $request->message
        );

        return response()->json(['message' => 'Alert sent to all users.']);
    }
}
