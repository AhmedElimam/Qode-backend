<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\UserInfoRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserPreferenceRepository;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private UserPreferenceRepository $userPreferenceRepository
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('user');

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->createdResponse([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'User registered successfully');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'User logged in successfully');
    }

    public function logout(LogoutRequest $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'User logged out successfully');
    }

    public function userInfo(UserInfoRequest $request): JsonResponse
    {
        return $this->resourceResponse(
            new UserResource($request->user()),
            'User information retrieved successfully'
        );
    }
}
