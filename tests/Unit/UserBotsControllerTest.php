<?php

namespace Tests\Unit;

use App\Models\UserBot;
use App\Models\User;
use App\Models\Bot;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserBotsControllerTest extends TestCase
{
    // Test to list all user bots
    public function test_it_can_list_all_user_bots()
    {
        // Insert dummy user bot data
        $user = User::create(['name' => 'Test User', 'email' => 'test@example.com', 'password' => bcrypt('password')]);
        $bot = Bot::create(['name' => 'Test Bot', 'description' => 'This is a test bot']);
        UserBot::create(['user_id' => $user->id, 'bot_id' => $bot->id, 'allocated_amount' => 1000, 'status' => 'active']);

        // Make API request to get user bots
        $response = $this->getJson('/api/user-bots');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'user_id',
                'bot_id',
                'allocated_amount',
                'status',
                'created_at',
                'updated_at'
            ]
        ]);
    }

    // Test to toggle activation status
    public function test_toggle_activation_successfully()
{
    // Create a dummy user
    $user = User::create(['name' => 'Test User', 'email' => 'test2@example.com', 'password' => bcrypt('password')]);

    // Create a dummy bot
    $bot = Bot::create(['name' => 'Sample Bot', 'description' => 'Sample Description']);

    // Create a dummy user bot
    $userBot = UserBot::create([
        'user_id' => $user->id,
        'bot_id' => $bot->id,
        'status' => 'inactive', // Initial status is inactive
    ]);

    // Acting as the created user
    $this->actingAs($user);

    // Make the PUT request to toggle the user bot activation
    $response = $this->putJson("/api/user-bots/{$bot->id}/toggle");

    // Assert the response status is 200
    $response->assertStatus(200);

    // Assert that the status was toggled to 'active'
    $this->assertDatabaseHas('user_bots', [
        'id' => $userBot->id,
        'status' => 'active',
    ]);

    // Assert the response contains the success message and updated user bot, without checking for all fields
    $response->assertJsonFragment([
        'user_bot' => [
            'status' => 'active', // Status should be active now
        ],
    ]);
}


    public function test_toggle_activation_not_found()
    {
        // Create a dummy user
        $user = User::create(['name' => 'Test User', 'email' => 'test3@example.com', 'password' => bcrypt('password')]);

        // Create a dummy bot but do not create a user bot
        $bot = Bot::create(['name' => 'Sample Bot', 'description' => 'Sample Description']);
        $this->actingAs($user);

        // Make the PUT request to toggle the user bot activation
        $response = $this->putJson("/api/user-bots/{$bot->id}/toggle");

        // Assert the response status is 404 (User Bot not found)
        $response->assertStatus(404);

        // Assert the response contains the not found message
        $response->assertJsonFragment([
            'message' => 'User Bot not found',
        ]);
    }

    // Test to store a new user bot
    public function test_it_can_store_a_new_user_bot()
    {
        $user = User::create(['name' => 'Test User', 'email' => 'test4@example.com', 'password' => bcrypt('password')]);
        $bot = Bot::create(['name' => 'Test Bot', 'description' => 'This is a test bot']);

        // Make API request to create a new user bot
        $response = $this->postJson('/api/user-bots', [
            'user_id' => $user->id,
            'bot_id' => $bot->id,
            'allocated_amount' => 1000,
            'status' => 'active'
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'user_id' => $user->id,
            'bot_id' => $bot->id,
            'allocated_amount' => 1000,
            'status' => 'active'
        ]);
    }

    // Test to show a specific user bot
    public function test_it_can_show_a_specific_user_bot()
    {
        $user = User::create(['name' => 'Test User', 'email' => 'test5@example.com', 'password' => bcrypt('password')]);
        $bot = Bot::create(['name' => 'Test Bot', 'description' => 'This is a test bot']);
        $userBot = UserBot::create(['user_id' => $user->id, 'bot_id' => $bot->id, 'allocated_amount' => 1000, 'status' => 'active']);

        // Make API request to get the specific user bot
        $response = $this->getJson("/api/user-bots/{$userBot->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'user_id' => $user->id,
            'bot_id' => $bot->id,
            'allocated_amount' => '1000.00',
            'status' => 'active'
        ]);
    }

    // Test to update a user bot
    public function test_it_can_update_a_user_bot()
    {
        $user = User::create(['name' => 'Test User', 'email' => 'test6@example.com', 'password' => bcrypt('password')]);
        $bot = Bot::create(['name' => 'Test Bot', 'description' => 'This is a test bot']);
        $userBot = UserBot::create(['user_id' => $user->id, 'bot_id' => $bot->id, 'allocated_amount' => 1000, 'status' => 'active']);

        // Make API request to update the user bot
        $response = $this->putJson("/api/user-bots/{$userBot->id}", [
            'allocated_amount' => 2000,
            'status' => 'inactive'
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'allocated_amount' => 2000,
            'status' => 'inactive'
        ]);
    }

    // Test to delete a user bot
    public function test_it_can_delete_a_user_bot()
    {
        $user = User::create(['name' => 'Test User', 'email' => 'test7@example.com', 'password' => bcrypt('password')]);
        $bot = Bot::create(['name' => 'Test Bot', 'description' => 'This is a test bot']);
        $userBot = UserBot::create(['user_id' => $user->id, 'bot_id' => $bot->id, 'allocated_amount' => 1000, 'status' => 'active']);

        // Make API request to delete the user bot
        $response = $this->deleteJson("/api/user-bots/{$userBot->id}");

        $response->assertStatus(204);

        // Ensure the user bot is deleted from the database
        $this->assertDatabaseMissing('user_bots', ['id' => $userBot->id]);
    }
}
