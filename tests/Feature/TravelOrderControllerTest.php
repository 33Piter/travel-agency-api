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
   | Tests for index method
   |--------------------------------------------------------------------------
   |
   */

    public function test_can_list_paginated_travel_orders_with_no_filters()
    {
        TravelOrder::factory(15)->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson(route('travel-order.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'applicant_name',
                        'applicant_email',
                        'destination',
                        'departure_date',
                        'return_date',
                        'status',
                    ],
                ],
                'meta',
                'links',
            ]);
    }

    public function test_can_filter_travel_orders_by_status()
    {
        TravelOrder::factory(5)->create([
            'user_id' => $this->user->id,
            'status' => TravelOrderStatusEnum::APPROVED->value,
        ]);

        TravelOrder::factory(5)->create([
            'user_id' => $this->user->id,
            'status' => TravelOrderStatusEnum::CANCELED->value,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson(route('travel-order.index', ['status' => TravelOrderStatusEnum::APPROVED->value]));

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonFragment(['status' => TravelOrderStatusEnum::APPROVED->value]);
    }

    public function test_can_filter_travel_orders_by_departure_date_range()
    {
        TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2025-01-15',
        ]);

        TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2025-02-10',
        ]);

        TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2025-03-01',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson(route('travel-order.index', [
                'departure_date_start' => '2025-02-01',
                'departure_date_end' => '2025-02-28',
            ]));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['departure_date' => '2025-02-10']);
    }

    public function test_returns_404_if_no_travel_orders_match_filters()
    {
        TravelOrder::factory(10)->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson(route('travel-order.index', ['departure_date_start' => '2100-01-01', 'departure_date_end' => '2100-12-31']));

        $response->assertStatus(404)
            ->assertJson(['message' => 'No travel orders found matching the provided criteria.']);
    }

    public function test_validates_request_data()
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson(route('travel-order.index', [
                'departure_date_start' => 'invalid-date',
                'departure_date_end' => '2025-01-01',
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'departure_date_start' => 'The departure date start must be in the format YYYY-MM-DD.',
            ]);
    }

    public function test_guest_cannot_access_travel_orders()
    {
        $response = $this->getJson(route('travel-order.index'));

        $response->assertStatus(401)
            ->assertJson(['error' => 'The informed token is not valid or the user is not authorized. Please log in to access your travel orders.']);
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

    /*
    |--------------------------------------------------------------------------
    | Tests for show method
    |--------------------------------------------------------------------------
    |
    */

    public function test_user_can_view_own_travel_order()
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson(route('travel-order.show', $this->travelOrder->id));

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'applicant_name', 'destination', 'departure_date', 'return_date', 'status']);
    }

    public function test_user_cannot_view_others_travel_order()
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->otherUserToken)
            ->getJson(route('travel-order.show', $this->travelOrder->id));

        $response->assertStatus(403)
            ->assertJson(['message' => 'You are not authorized to view this travel order.']);
    }

    public function test_guest_cannot_view_travel_order()
    {
        $response = $this->getJson(route('travel-order.show', $this->travelOrder->id));

        $response->assertStatus(401)
            ->assertJson(['error' => 'The informed token is not valid or the user is not authorized. Please log in to access your travel orders.']);
    }

    /*
    |--------------------------------------------------------------------------
    | Tests for update method
    |--------------------------------------------------------------------------
    |
    */

    public function test_user_can_update_travel_order_status()
    {
        $newStatus = TravelOrderStatusEnum::APPROVED->value;

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->patchJson(route('travel-order.update', $this->travelOrder->id), ['status' => $newStatus]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Travel order status updated successfully.',
                'travel_order' => [
                    'id' => $this->travelOrder->id,
                    'status' => $newStatus,
                ],
            ]);

        $this->assertDatabaseHas('travel_orders', [
            'id' => $this->travelOrder->id,
            'status' => $newStatus,
        ]);
    }

    public function test_user_cannot_update_travel_order_status_with_same_status()
    {
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => TravelOrderStatusEnum::APPROVED->value,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->patchJson(route('travel-order.update', $travelOrder->id), ['status' => TravelOrderStatusEnum::APPROVED->value]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'The travel order status is already set to '.TravelOrderStatusEnum::APPROVED->value.'.',
            ]);

        $this->assertDatabaseHas('travel_orders', [
            'id' => $travelOrder->id,
            'status' => TravelOrderStatusEnum::APPROVED->value,
        ]);
    }

    public function test_user_cannot_update_travel_order_status_of_another_user()
    {
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->otherUser->id,
            'status' => TravelOrderStatusEnum::REQUESTED->value,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->patchJson(route('travel-order.update', $travelOrder->id), ['status' => TravelOrderStatusEnum::APPROVED->value]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You are not authorized to update this travel order.',
            ]);

        $this->assertDatabaseHas('travel_orders', [
            'id' => $travelOrder->id,
            'status' => TravelOrderStatusEnum::REQUESTED->value,
        ]);
    }

    public function test_user_cannot_update_travel_order_status_with_invalid_data()
    {
        $invalidStatus = 'invalid_status';

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->patchJson(route('travel-order.update', $this->travelOrder->id), ['status' => $invalidStatus]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'status' => 'The status must be one of the following: '
                    .TravelOrderStatusEnum::APPROVED->value.' or '.TravelOrderStatusEnum::CANCELED->value,
            ]);

        $this->assertDatabaseHas('travel_orders', [
            'id' => $this->travelOrder->id,
            'status' => TravelOrderStatusEnum::REQUESTED->value,
        ]);
    }

    public function test_guest_cannot_update_travel_order_status()
    {
        $response = $this->patchJson(route('travel-order.update', $this->travelOrder->id), [
            'status' => TravelOrderStatusEnum::APPROVED->value,
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'The informed token is not valid or the user is not authorized. Please log in to access your travel orders.']);

        $this->assertDatabaseHas('travel_orders', [
            'id' => $this->travelOrder->id,
            'status' => TravelOrderStatusEnum::REQUESTED->value,
        ]);
    }

}
