<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeAlpacaKey(Request $request)
{
    // Validate the request
    $request->validate([
        'api_key' => 'required|string',
        'api_secret' => 'required|string',
    ]);

    // Store the API key and secret for the user
    $apiKey = new ApiKey();
    $apiKey->user_id = auth()->id();  // Associate with the logged-in user
    $apiKey->api_key = Crypt::encryptString($request->api_key);  // Encrypt the API key
    $apiKey->api_secret = Crypt::encryptString($request->api_secret);  // Encrypt the API secret
    $apiKey->save();

    return response()->json(['message' => 'API key stored successfully'], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
