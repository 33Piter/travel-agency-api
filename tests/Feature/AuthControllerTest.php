<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->getToken($this->user->email, 'password');
    }

    private function getToken(string $email, string $password): string
    {
        $response = $this->postJson(route('auth.login'), [
            'email' => $email,
            'password' => $password,
        ]);

        return $response->json('token');
    }

    /*
    |--------------------------------------------------------------------------
    | Tests for register method
    |--------------------------------------------------------------------------
    |
    */

    public function test_user_can_register()
    {
        $response = $this->postJson(route('auth.register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                'token',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_registration_fails_with_invalid_data()
    {
        $response = $this->postJson(route('auth.register'), [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure(['name', 'email', 'password']);
    }

    /*
    |--------------------------------------------------------------------------
    | Tests for login method
    |--------------------------------------------------------------------------
    |
    */

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials']);
    }

    /*
    |--------------------------------------------------------------------------
    | Tests for getUser method
    |--------------------------------------------------------------------------
    |
    */

    public function test_authenticated_user_can_get_user()
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson(route('auth.user'));

        $response->assertStatus(200)
            ->assertJson([
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]);
    }

    public function test_get_user_fails_with_invalid_token()
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson(route('auth.user'));

        $response->assertStatus(401)
            ->assertJson(['error' => 'The informed token is not valid or the user is not authorized. Please log in to access your travel orders.']);
    }
}
