<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use PHPUnit\Framework\Attributes\Test;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::insert([
            ['id' => 1, 'name' => 'super_admin'],
            ['id' => 2, 'name' => 'manager'],
            ['id' => 3, 'name' => 'employee']
        ]);
    }

    #[Test]
    public function user_can_register()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => 3
        ];

        $response = $this->postJson('api/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status_code',
                'status_message',
                'data' => [
                    'token',
                    'type'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'role_id' => 3
        ]);
    }

    #[Test]
    public function user_cannot_register_with_existing_email()
    {
        // Create existing user
        User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $userData = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => Hash::make('password123'),
            'role_id' => 3
        ];

        $response = $this->postJson('api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('api/auth/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status_code',
                'status_message',
                'data' => [
                    'token',
                    'type'
                ]
            ]);
    }

    #[Test]
    public function user_cannot_login_with_invalid_credentials()
    {
        $loginData = [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('api/auth/login', $loginData);

        $response->assertStatus(401)
            ->assertJson([
                'status_code' => 401,
                'status_message' => 'Unauthorized',
                'message' => 'Invalid email or password'
            ]);
    }

    #[Test]
    public function user_can_logout()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status_code' => 200,
                'status_message' => 'User logged out successfully',
                'data' => []
            ]);
    }

    #[Test]
    public function user_can_refresh_token()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('api/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status_code',
                'status_message',
                'data' => [
                    'token',
                    'type'
                ]
            ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->postJson('api/auth/logout');
        $response->assertStatus(401);

        $response = $this->postJson('api/auth/refresh');
        $response->assertStatus(401);
    }
}