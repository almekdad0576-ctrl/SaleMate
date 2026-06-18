<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(protected UserService $service)
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $login = $this->service->login($request->validated());

        return $this->respond(true, 'Login successful.', [
            'user' => $login['user'],
            'access_token' => $login['token'],
            'token_type' => 'bearer',
        ], 200);
    }

    public function logout(): JsonResponse
    {
        $this->service->logout();

        return $this->respond(true, 'Logged out successfully.', [], 200);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $this->service->profile($request->user());

        return $this->respond(true, 'Profile fetched successfully.', ['user' => $user], 200);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->service->updateProfile($request->user(), $request->validated());

        return $this->respond(true, 'Profile updated successfully.', ['user' => $user], 200);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->service->changePassword(
            $request->user(),
            $request->validated()['current_password'],
            $request->validated()['password'],
        );

        return $this->respond(true, 'Password updated successfully.', [], 200);
    }

    public function index(Request $request): JsonResponse
    {
        $this->service->ensureSuperAdmin($request->user());

        return $this->respond(true, 'Users retrieved successfully.', ['users' => $this->service->listUsers()], 200);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->service->ensureSuperAdmin($request->user());

        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = $this->service->createUser($data);

        return $this->respond(true, 'User created successfully.', ['user' => $user], 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $this->service->ensureSuperAdmin($request->user());

        return $this->respond(true, 'User retrieved successfully.', ['user' => $this->service->getUser($user)], 200);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->service->ensureSuperAdmin($request->user());

        $user = $this->service->updateUser($user, $request->validated());

        return $this->respond(true, 'User updated successfully.', ['user' => $user], 200);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->service->ensureSuperAdmin($request->user());
        $this->service->deleteUser($user);

        return $this->respond(true, 'User deleted successfully.', [], 200);
    }
}
