<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'user_type' => $user->user_type,
                    'management_subrole' => $user->management_subrole,
                    'is_verified' => $user->is_verified,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Register customer (only customers can self-register)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Only customers can self-register
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'user_type' => 'customer', // Force customer type
            'is_verified' => false, // Need admin verification
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please wait for admin verification.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'user_type' => $user->user_type,
                    'is_verified' => $user->is_verified,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 201);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'bank_account' => $user->bank_account,
                    'user_type' => $user->user_type,
                    'management_subrole' => $user->management_subrole,
                    'is_verified' => $user->is_verified,
                    'verified_at' => $user->verified_at,
                ]
            ]
        ]);
    }

    /**
     * Admin: Create user (Petani, Management, Admin)
     */
    public function createUser(Request $request)
    {
        // Check if user is admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admin can create users.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'user_type' => 'required|in:admin,petani,management',
            'management_subrole' => 'required_if:user_type,management|in:gudang_in,gudang_out,produksi,pemasaran',
            'bank_account' => 'required_if:user_type,petani|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'user_type' => $request->user_type,
            'is_verified' => true, // Admin-created users are auto-verified
            'verified_at' => now(),
        ];

        if ($request->user_type === 'management') {
            $userData['management_subrole'] = $request->management_subrole;
        }

        if ($request->user_type === 'petani') {
            $userData['bank_account'] = $request->bank_account;
        }

        $user = User::create($userData);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'user_type' => $user->user_type,
                    'management_subrole' => $user->management_subrole,
                    'is_verified' => $user->is_verified,
                ]
            ]
        ], 201);
    }

    /**
     * Admin: Get all users
     */
    public function getAllUsers(Request $request)
    {
        // Check if user is admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admin can view all users.'
            ], 403);
        }

        $users = User::select('id', 'name', 'email', 'phone', 'address', 'user_type', 'management_subrole', 'is_verified', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users
            ]
        ]);
    }

    /**
     * Admin: Verify user
     */
    public function verifyUser(Request $request, $userId)
    {
        // Check if user is admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admin can verify users.'
            ], 403);
        }

        $user = User::findOrFail($userId);
        
        $user->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User verified successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_verified' => $user->is_verified,
                ]
            ]
        ]);
    }
}
