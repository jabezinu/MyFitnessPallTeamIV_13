<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'height_cm' => 'nullable|integer|min:50|max:300',
            'activity_level' => 'nullable|in:sedentary,light,moderate,active,very_active',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'height_cm' => $request->height_cm,
            'activity_level' => $request->activity_level ?? 'moderate',
            'role' => 'user',
            'is_active' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Log successful registration
        LoginAttempt::create([
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'success' => true,
            'user_agent' => $request->userAgent(),
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 201);
    }

    /**
     * Authenticate user and return token
     */
    public function login(Request $request): JsonResponse
    {
        // Rate limiting
        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many login attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        
        // Log the attempt
        $attempt = [
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $user?->id,
        ];

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 300); // 5 minutes
            
            LoginAttempt::create(array_merge($attempt, [
                'success' => false,
                'failure_reason' => 'Invalid credentials',
            ]));

            // Increment failed attempts for user
            if ($user) {
                $user->increment('failed_login_attempts');
                
                // Lock account after 5 failed attempts
                if ($user->failed_login_attempts >= 5) {
                    $user->update([
                        'account_locked_until' => now()->addMinutes(30)
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Check if account is locked
        if ($user->isAccountLocked()) {
            LoginAttempt::create(array_merge($attempt, [
                'success' => false,
                'failure_reason' => 'Account locked',
            ]));

            return response()->json([
                'success' => false,
                'message' => 'Account is temporarily locked. Please try again later.',
            ], 423);
        }

        // Check if account is active
        if (!$user->isActive()) {
            LoginAttempt::create(array_merge($attempt, [
                'success' => false,
                'failure_reason' => 'Account inactive',
            ]));

            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated. Please contact support.',
            ], 403);
        }

        // Successful login
        RateLimiter::clear($key);
        
        // Reset failed attempts and update last login
        $user->update([
            'failed_login_attempts' => 0,
            'account_locked_until' => null,
            'last_login' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        LoginAttempt::create(array_merge($attempt, [
            'success' => true,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Logout user and revoke token
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices successfully',
        ]);
    }

    /**
     * Get authenticated user info
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'date_of_birth' => $user->date_of_birth,
                    'gender' => $user->gender,
                    'height_cm' => $user->height_cm,
                    'activity_level' => $user->activity_level,
                    'role' => $user->role,
                    'last_login' => $user->last_login,
                    'created_at' => $user->created_at,
                ]
            ]
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }
}
