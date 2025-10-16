<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\UserUpdateProfileRequest;
use App\Http\Requests\Api\UserChangePasswordRequest;
use App\Http\Requests\Api\UserRegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * User API Controller
 * 
 * Handles user-related API operations for frontend applications
 * including profile management, password changes, and user registration.
 */
class UserApiController extends Controller
{
    /**
     * Get user profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            $user->photo_url = $user->photo ? asset('storage/' . $user->photo) : null;
            
            return response()->json($user);
        } catch (\Exception $e) {
            Log::error('User profile API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve user profile'], 500);
        }
    }

    /**
     * Update user profile
     *
     * @param UserUpdateProfileRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(UserUpdateProfileRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $user = $request->user();
            
            // Check if email is being changed and if it's unique
            if ($validatedData['email'] !== $user->email) {
                $existingUser = User::where('email', $validatedData['email'])
                    ->where('id', '!=', $user->id)
                    ->first();
                    
                if ($existingUser) {
                    return response()->json(['message' => 'Email already exists'], 400);
                }
            }
            
            $user->update([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'] ?? null,
            ]);

            $user->photo_url = $user->photo ? asset('storage/' . $user->photo) : null;

            return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
        } catch (\Exception $e) {
            Log::error('User update profile API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update profile'], 500);
        }
    }

    /**
     * Change user password
     *
     * @param UserChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(UserChangePasswordRequest $request)
    {
        try {
            $data = $request->validated();
            $user = $request->user();

            if (!Hash::check($data['current_password'], $user->password)) {
                return response()->json(['message' => 'Current password is incorrect'], 400);
            }

            $user->update([
                'password' => Hash::make($data['new_password'])
            ]);

            return response()->json(['message' => 'Password changed successfully']);
        } catch (\Exception $e) {
            Log::error('User change password API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to change password'], 500);
        }
    }

    /**
     * Register new user
     *
     * @param UserRegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(UserRegisterRequest $request)
    {
        try {
            $data = $request->validated();

            // Check if email already exists
            $existingUser = User::where('email', $data['email'])->first();
            if ($existingUser) {
                return response()->json(['message' => 'Email already exists'], 400);
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'role' => 'user',
                'status' => 'active',
            ]);

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'message' => 'User registered successfully',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('User register API error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to register user'], 500);
        }
    }
}
