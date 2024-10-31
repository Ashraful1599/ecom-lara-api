<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'nullable|string',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        // Check if the user has the role 'administrator'
        if ($user->role === 'administrator') {
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json(['user' => $user, 'token' => $token], 200);
        }

        // If the user is not an administrator, return a different response
        return response()->json(['message' => 'Access denied: User is not an administrator'], 403);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
    public function unauthenticated()
    {
        return response()->json([
            "status" => false,
            "message" => "Unauthenticated. Please login first",
        ], 401);
    }

    public  function  index()
    {
        $users  = User::all();
        return response()->json($users,200 );
    }

    public  function show($id)
    {
      $user = User::all()->findOrFail($id);

      return response()->json($user, 200);
    }

    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // Validate the incoming request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $userId,
            'role' => 'nullable|string',
            'password' => 'nullable|string|min:8', // Make password nullable for optional updates
        ]);

        // Check if password is present in the request, and hash it if so
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']); // Exclude password if not provided
        }

        // Update the user with the validated and possibly hashed data
        $user->update($validated);

        return response()->json($user, 200);
    }

    public  function  destroy($id)
    {
      $user = User::destroy($id);
      return response()->json($user, 200);
    }

    public function bulkDeleteUsers(Request $request)
    {
        $ids = $request->input('ids'); // Get array of IDs from request
        if (is_array($ids) && count($ids) > 0) {
            $deletedCount = User::destroy($ids); // Delete multiple products with an array of IDs
            return response()->json(['deleted' => $deletedCount], 200);
        } else {
            return response()->json(['error' => 'No valid IDs provided'], 400);
        }
    }



}
