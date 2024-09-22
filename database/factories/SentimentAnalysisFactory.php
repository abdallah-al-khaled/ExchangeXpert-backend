<?php

namespace Database\Factories;

use App\Models\SentimentAnalysis;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SentimentAnalysisFactory extends Factory
{
    protected $model = SentimentAnalysis::class;

    public function definition()
    {
        return [
            'stock_symbol' => strtoupper($this->faker->randomElement(['AAPL', 'TSLA', 'GOOGL', 'AMZN'])),
            'sentiment_score' => $this->faker->numberBetween(-100, 100),
            'analysis_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
