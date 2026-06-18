<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function login(array $data): array
    {
        $credentials = [
            'email' => $data['email'],
            'password' => $data['password'],
        ];

        $guard = Auth::guard('api');

        if (! $token = $guard->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return [
            'user' => $guard->user(),
            'token' => $token,
        ];
    }

    public function logout(): void
    {
        Auth::guard('api')->logout();
    }

    public function profile(User $user): User
    {
        return $user;
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->fill($data);
        $user->save();

        return $user;
    }

    public function changePassword(User $user, string $currentPassword, string $password): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->password = $password;
        $user->save();
    }

    public function listUsers()
    {
        return User::orderBy('created_at', 'desc')->get();
    }

    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function getUser(User $user): User
    {
        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        $user->fill($data);
        $user->save();

        return $user;
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }

    public function ensureSuperAdmin(User $user): void
    {
        if ($user->role !== 'super_admin') {
            throw new AuthorizationException('Only super admins may perform this action.');
        }
    }
}
