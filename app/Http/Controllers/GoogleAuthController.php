<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        $parameters = [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'redirect_uri' => 'https://utspbfdevi1200.sistemapotekmitragaluh.com/api/oauth/register/call-back',
            'response_type' => 'code',
            'scope' => 'email profile',
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
            'state' => 'state_parameter_passthrough_value',
            'prompt' => 'consent' // Ensure the consent screen is shown
        ];

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($parameters);
        return response()->json(['redirect_url' => $authUrl]);
    }

    public function callbackGoogle(Request $request)
    {
        $code = $request->input('code');

        if (!$code) {
            return response()->json(['error' => 'Authorization code not found'], 400);
        }

        try {
            $client = new Client();

            $response = $client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'code' => $code,
                    'client_id' => env('GOOGLE_CLIENT_ID'),
                    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                    'redirect_uri' => 'https://utspbfdevi1200.sistemapotekmitragaluh.com/api/oauth/register/call-back',
                    'grant_type' => 'authorization_code',
                    'access_type' => 'offline',
                ],
            ]);

            $tokenData = json_decode($response->getBody(), true);
            $accessToken = $tokenData['access_token'];

            // Use the access token to get user information
            $google_user = Socialite::driver('google')->stateless()->userFromToken($accessToken);

            // Check if a user with the same email already exists
            $user = User::where('email', $google_user->getEmail())->first();

            if (!$user) {
                // Check if a user with the same Google ID already exists
                $user = User::where('google_id', $google_user->getId())->first();

                if (!$user) {
                    // Create a new user if no matching email or Google ID is found
                    $user = User::create([
                        'name' => $google_user->getName(),
                        'email' => $google_user->getEmail(),
                        'google_id' => $google_user->getId(),
                        'role' => 'user', // Assuming a default role of 'user'
                    ]);
                } else {
                    // Update the user's email if it was different
                    $user->update(['email' => $google_user->getEmail()]);
                }
            }

            $payload = [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'iat' => Carbon::now()->timestamp,
                'exp' => Carbon::now()->timestamp + 3600,
            ];

            $token = JWT::encode($payload, env('JWT_SECRET_KEY'), 'HS256');

            Auth::login($user);
            return response()->json(['message' => 'User logged in successfully', 'user' => $user, 'bearer token' => $token], 200);

        } catch (\Throwable $th) {
            return response()->json(['error' => 'Something went wrong', 'details' => $th->getMessage()], 424);
        }
    }
}