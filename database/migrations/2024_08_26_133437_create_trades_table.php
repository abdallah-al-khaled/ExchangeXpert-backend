<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_bot_id')->constrained()->onDelete('cascade');
            $table->string('stock_symbol'); // Stock identifier (e.g., AAPL)
            $table->enum('action', ['buy', 'sell']);
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->timestamp('buy_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
