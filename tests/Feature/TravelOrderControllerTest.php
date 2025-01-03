<?php

namespace Tests\Feature;

use App\Enums\TravelOrderStatusEnum;
use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected string $token;
    protected string $otherUserToken;
    protected TravelOrder $travelOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        $this->token = $this->getToken($this->user->email, 'password');
        $this->otherUserToken = $this->getToken($this->otherUser->email, 'password');

        $this->travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => TravelOrderStatusEnum::REQUESTED->value,
            'departure_date' => '2025-05-01', ]);
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
    | Tests for create method
    |--------------------------------------------------------------------------
    |
    */

    public function test_user_can_create_travel_order_with_valid_data()
    {
        $data = [
            'applicant_name' => 'John Doe',
            'applicant_email' => 'john.doe@example.com',
            'destination' => 'Paris',
            'departure_date' => now()->addDays(1)->toDateString(),
            'return_date' => now()->addDays(5)->toDateString(),
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson(route('travel-order.store'), $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Travel order created successfully.',
                'data' => [
                    'applicant_name' => $data['applicant_name'],
                    'applicant_email' => $data['applicant_email'],
                    'destination' => $data['destination'],
                    'departure_date' => $data['departure_date'],
                    'return_date' => $data['return_date'],
                    'status' => TravelOrderStatusEnum::REQUESTED->value,
                ],
            ]);

        $this->assertDatabaseHas('travel_orders', [
            'applicant_name' => $data['applicant_name'],
            'applicant_email' => $data['applicant_email'],
            'destination' => $data['destination'],
            'departure_date' => $data['departure_date'],
            'return_date' => $data['return_date'],
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_cannot_create_travel_order_with_invalid_data()
    {
        $data = [
            'applicant_name' => '',
            'applicant_email' => 'invalid-email',
            'destination' => '',
            'departure_date' => now()->subDays(1)->toDateString(),
            'return_date' => now()->subDays(2)->toDateString(),
        ];

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson(route('travel-order.store'), $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'applicant_name' => 'The applicant name is required.',
                'applicant_email' => 'The applicant email must be a valid email address.',
                'destination' => 'The destination is required.',
                'departure_date' => 'The departure date must be a date after or equal to today.',
                'return_date' => 'The return date must be a date after or equal to departure date.',
            ]);
    }

    public function test_guest_cannot_create_travel_order()
    {
        $data = [
            'applicant_name' => 'Jane Doe',
            'applicant_email' => 'jane.doe@example.com',
            'destination' => 'London',
            'departure_date' => now()->addDays(2)->toDateString(),
            'return_date' => now()->addDays(6)->toDateString(),
        ];

        $response = $this->postJson(route('travel-order.store'), $data);

        $response->assertStatus(401)
            ->assertJson(['error' => 'The informed token is not valid or the user is not authorized. Please log in to access your travel orders.']);
    }
}
