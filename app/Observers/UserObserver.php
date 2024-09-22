<?php
namespace App\Observers;

use App\Models\Bot;
use App\Models\User;
use App\Models\UserBot;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        // Get all bots
        $bots = Bot::all();

        // Loop through each bot and create a record in the user_bots table
        foreach ($bots as $bot) {
            UserBot::create([
                'user_id' => $user->id,
                'bot_id' => $bot->id,
                'allocated_amount' => 0,  // You can set a default amount here
                'status' => 'inactive',   // Set initial status as inactive
            ]);
        }
    }
}
