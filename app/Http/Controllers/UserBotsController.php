<?php

namespace App\Http\Controllers;
use App\Models\UserBot;
use Illuminate\Http\Request;

class UserBotsController extends Controller
{
    public function index()
    {
        $userBots = UserBot::with('user', 'bot')->get();
        return response()->json($userBots);
    }
}
