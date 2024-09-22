<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Bot;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BotsControllerTest extends TestCase
{
    use RefreshDatabase;  // Ensures a clean database for each test

    /** @test */
    public function it_can_list_all_bots()
    {
        // Insert dummy bot data
        Bot::create(['name' => 'Test Bot', 'description' => 'Test Description']);
        Bot::create(['name' => 'Sample Bot', 'description' => 'Sample Description']);

        // Make GET request to list all bots
        $response = $this->getJson('/api/bots');

        // Assert response status and structure
        $response->assertStatus(200)
                 ->assertJsonCount(2)  // Assert there are 2 bots
                 ->assertJsonFragment(['name' => 'Test Bot'])
                 ->assertJsonFragment(['name' => 'Sample Bot']);
    }

    /** @test */
    public function it_can_create_a_bot()
    {
        // Bot data
        $botData = [
            'name' => 'New Bot',
            'description' => 'New Bot Description',
        ];

        // Make POST request to store the bot
        $response = $this->postJson('/api/bots', $botData);

        // Assert response status and content
        $response->assertStatus(201)
                 ->assertJsonFragment($botData);

        // Check if the bot was created in the database
        $this->assertDatabaseHas('bots', $botData);
    }

    /** @test */
    public function it_can_show_a_bot()
    {
        // Insert dummy bot data
        $bot = Bot::create(['name' => 'Test Bot', 'description' => 'Test Description']);

        // Make GET request to show the specific bot
        $response = $this->getJson("/api/bots/{$bot->id}");

        // Assert response status and content
        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Test Bot'])
                 ->assertJsonFragment(['description' => 'Test Description']);
    }

    /** @test */
    public function it_can_update_a_bot()
    {
        // Insert dummy bot data
        $bot = Bot::create(['name' => 'Old Bot', 'description' => 'Old Description']);

        // Updated bot data
        $updatedData = [
            'name' => 'Updated Bot',
            'description' => 'Updated Description',
        ];

        // Make PUT request to update the bot
        $response = $this->putJson("/api/bots/{$bot->id}", $updatedData);

        // Assert response status and updated content
        $response->assertStatus(200)
                 ->assertJsonFragment($updatedData);

        // Check if the bot was updated in the database
        $this->assertDatabaseHas('bots', $updatedData);
    }

    /** @test */
    public function it_can_delete_a_bot()
    {
        // Insert dummy bot data
        $bot = Bot::create(['name' => 'Bot to delete', 'description' => 'Will be deleted']);

        // Make DELETE request to destroy the bot
        $response = $this->deleteJson("/api/bots/{$bot->id}");

        // Assert response status and ensure the bot was deleted
        $response->assertStatus(204);

        // Check if the bot was removed from the database
        $this->assertDatabaseMissing('bots', ['id' => $bot->id]);
    }
}
