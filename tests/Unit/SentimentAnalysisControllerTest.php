<?php

namespace Tests\Unit;

use App\Models\SentimentAnalysis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class SentimentAnalysisControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_store_a_sentiment_analysis()
    {
        $data = [
            'stock_symbol' => 'AAPL',
            'sentiment_score' => 75,
            'analysis_date' => now()->toDateString(),
        ];

        $response = $this->postJson('/api/sentiment-analysis', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment($data);

        $this->assertDatabaseHas('sentiment_analysis', $data);
    }

    /** @test */
    public function it_validates_store_request()
    {
        $data = [
            'stock_symbol' => '',  // Invalid empty stock symbol
            'sentiment_score' => -200,  // Invalid score out of range
            'analysis_date' => '2022--01',  // Invalid date format
        ];

        $response = $this->postJson('/api/sentiment-analysis', $data);

        $response->assertStatus(422);
                 
    }

    /** @test */
    public function it_can_get_latest_sentiment_for_a_stock()
    {
        $stockSymbol = 'AAPL';

        // Insert dummy data directly
        SentimentAnalysis::create(['stock_symbol' => $stockSymbol, 'sentiment_score' => '0.7', 'created_at' => now()->subDay()]);
        SentimentAnalysis::create(['stock_symbol' => $stockSymbol, 'sentiment_score' => '0.9', 'created_at' => now()]);

        $response = $this->getJson("/api/sentiment-analysis/{$stockSymbol}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['sentiment_score' => '0.70', 'stock_symbol' => $stockSymbol]);
    }

    /** @test */
    public function it_returns_404_if_no_latest_sentiment_for_stock()
    {
        $response = $this->getJson("/api/sentiment-analysis/INVALID");

        $response->assertStatus(404)
                 ->assertJson(['message' => 'No sentiment analysis found for the stock']);
    }

    /** @test */
    public function it_can_get_top_stocks_by_sentiment()
    {
        // Insert dummy data directly
        SentimentAnalysis::create(['stock_symbol' => 'AAPL', 'sentiment_score' => 90, 'created_at' => now()]);
        SentimentAnalysis::create(['stock_symbol' => 'TSLA', 'sentiment_score' => 85, 'created_at' => now()]);
        SentimentAnalysis::create(['stock_symbol' => 'GOOGL', 'sentiment_score' => 70, 'created_at' => now()]);

        $response = $this->getJson('/api/sentiment-analysis/top');

        $response->assertStatus(200)
                 ->assertJsonCount(3)  // Assuming the limit is set to 3 or more
                 ->assertJsonFragment(['stock_symbol' => 'AAPL']);
    }

    /** @test */
    public function it_can_get_worst_stocks_by_sentiment()
    {
        // Insert dummy data directly
        SentimentAnalysis::create(['stock_symbol' => 'AAPL', 'sentiment_score' => 10, 'created_at' => now()]);
        SentimentAnalysis::create(['stock_symbol' => 'TSLA', 'sentiment_score' => 5, 'created_at' => now()]);
        SentimentAnalysis::create(['stock_symbol' => 'GOOGL', 'sentiment_score' => -10, 'created_at' => now()]);

        $response = $this->getJson('/api/sentiment-analysis/worst');

        $response->assertStatus(200)
                 ->assertJsonCount(3)  // Assuming the limit is set to 3 or more
                 ->assertJsonFragment(['stock_symbol' => 'GOOGL']);
    }

}