<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');
        $this->superAdminPassword = 'supersecret123';
        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@gmail.com',
            'name' => 'superadmin',
            'password' => Hash::make($this->superAdminPassword),
            'role' => 'super_admin',
        ]);
    }

    private function loginAsSuperAdmin(): string
    {
        $user = $this->superAdmin;

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $this->superAdminPassword,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'msg' => 'Login successful.'])
            ->assertJsonStructure([
                'success',
                'msg',
                'data' => [
                    'user' => ['id', 'email'],
                    'access_token',
                    'token_type',
                ],
            ]);

        return $response->json('data.access_token');
    }

    private function authHeaders(string $token): array
    {
        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_login_profile_update_password_and_logout_flow(): void
    {
        $superAdmin = $this->superAdmin;
        $originalPassword = $this->superAdminPassword;

        $loginResponse = $this->postJson('/api/login', [
            'email' => $superAdmin->email,
            'password' => $originalPassword,
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson(['success' => true, 'msg' => 'Login successful.']);

        $token = $loginResponse->json('data.access_token');
        $this->assertNotNull($token, 'Token should be returned');
        $this->assertIsString($token, 'Token should be a string');
        $loginResponse->assertJsonStructure([
            'data' => [
                'user' => ['id', 'email', 'name', 'role'],
                'access_token',
                'token_type',
            ],
        ]);

        // Verify the logged-in user in response matches the seeded user
        $this->assertEquals($superAdmin->id, $loginResponse->json('data.user.id'));
        $this->assertEquals($superAdmin->email, $loginResponse->json('data.user.email'));

        // Get profile and verify returned user is correct
        $profileResponse = $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/profile');

        $profileResponse->assertStatus(200)
            ->assertJson(['success' => true, 'msg' => 'Profile fetched successfully.']);

        $profileData = $profileResponse->json('data.user');
        $this->assertEquals($superAdmin->id, $profileData['id']);
        $this->assertEquals($superAdmin->email, $profileData['email']);

        // Update profile and verify database change
        $newName = 'Super Admin Updated';
        $updateProfileResponse = $this->withHeaders($this->authHeaders($token))
            ->putJson('/api/profile', [
                'name' => $newName,
            ]);

        $updateProfileResponse->assertStatus(200)
            ->assertJson(['success' => true, 'msg' => 'Profile updated successfully.']);

        $updatedInDb = $superAdmin->fresh();
        $this->assertEquals($newName, $updatedInDb->name, 'Name should be updated in database');
        $this->assertEquals($newName, $updateProfileResponse->json('data.user.name'));

        // Change password and verify it actually changed
        $newPassword = 'newpassword123';
        $changePasswordResponse = $this->withHeaders($this->authHeaders($token))
            ->putJson('/api/password', [
                'current_password' => $originalPassword,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ]);

        $changePasswordResponse->assertStatus(200)
            ->assertJson(['success' => true, 'msg' => 'Password updated successfully.']);

        $userWithNewPassword = User::query()->findOrFail($superAdmin->id);
        $this->assertTrue(
            Hash::check($newPassword, $userWithNewPassword->password),
            'New password should be hashed correctly in database'
        );
        $this->assertFalse(
            Hash::check($originalPassword, $userWithNewPassword->password),
            'Old password should no longer work'
        );

        // Logout and verify token is invalidated
        $logoutResponse = $this->withHeaders($this->authHeaders($token))
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson(['success' => true, 'msg' => 'Logged out successfully.']);

        // Verify token is blacklisted by trying to use it again
        $postLogoutResponse = $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/profile');

        $postLogoutResponse->assertStatus(401);
    }

    public function test_super_admin_can_crud_users(): void
    {
        $token = $this->loginAsSuperAdmin();

        // CREATE: Verify user is created in database with correct data
        $createData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ];

        $createResponse = $this->withHeaders($this->authHeaders($token))
            ->postJson('/api/users', $createData);

        $createResponse->assertStatus(201)
            ->assertJson(['success' => true, 'msg' => 'User created successfully.']);

        $userId = $createResponse->json('data.user.id');
        $this->assertNotNull($userId);

        // Verify created user exists in database with correct values
        $createdUser = User::query()->findOrFail($userId);
        $this->assertEquals($createData['name'], $createdUser->name);
        $this->assertEquals($createData['email'], $createdUser->email);
        $this->assertEquals($createData['role'], $createdUser->role);
        $this->assertTrue(Hash::check($createData['password'], $createdUser->password));

        // LIST: Verify user is in the list
        $listResponse = $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/users');

        $listResponse->assertStatus(200)
            ->assertJson(['success' => true, 'msg' => 'Users retrieved successfully.']);

        $userIds = collect($listResponse->json('data.users'))->pluck('id')->toArray();
        $this->assertContains($userId, $userIds, 'Created user should be in the list');

        // SHOW: Verify specific user details match database
        $showResponse = $this->withHeaders($this->authHeaders($token))
            ->getJson("/api/users/{$userId}");

        $showResponse->assertStatus(200)
            ->assertJson(['success' => true, 'msg' => 'User retrieved successfully.']);

        $showData = $showResponse->json('data.user');
        $this->assertEquals($userId, $showData['id']);
        $this->assertEquals($createData['name'], $showData['name']);
        $this->assertEquals($createData['email'], $showData['email']);
        $this->assertEquals($createData['role'], $showData['role']);

        // UPDATE: Verify user is updated in database
        $updatedName = 'Updated Test User';
        $updateResponse = $this->withHeaders($this->authHeaders($token))
            ->putJson("/api/users/{$userId}", [
                'name' => $updatedName,
            ]);

        $updateResponse->assertStatus(200)
            ->assertJson(['success' => true, 'msg' => 'User updated successfully.']);

        $updatedUser = User::query()->findOrFail($userId);
        $this->assertEquals($updatedName, $updatedUser->name, 'Name should be updated in database');
        $this->assertEquals($updatedName, $updateResponse->json('data.user.name'));

        // Verify other fields remain unchanged
        $this->assertEquals($createData['email'], $updatedUser->email);
        $this->assertEquals($createData['role'], $updatedUser->role);

        // DELETE: Verify user is soft-deleted
        $deleteResponse = $this->withHeaders($this->authHeaders($token))
            ->deleteJson("/api/users/{$userId}");

        $deleteResponse->assertStatus(200)
            ->assertJson(['success' => true, 'msg' => 'User deleted successfully.']);

        // Verify user is soft-deleted (not hard-deleted)
        $deletedUser = User::query()->withTrashed()->findOrFail($userId);
        $this->assertNotNull($deletedUser->deleted_at, 'User should be soft-deleted');

        // Verify deleted user is not in normal list
        $listAfterDelete = $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/users');

        $userIdsAfterDelete = collect($listAfterDelete->json('data.users'))->pluck('id')->toArray();
        $this->assertNotContains($userId, $userIdsAfterDelete, 'Deleted user should not appear in list');
    }
}
