<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Check the email and password, then hand out a token.
    // Unknown email and wrong password give the same answer, so nobody can probe which emails exist.
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Wrong email or password.'], 401);
        }

        return response()->json([
            'token' => $user->createToken($data['device_name'])->plainTextToken,
            'name' => $user->name,
        ], 201);
    }

    // Who does this token belong to?
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);
    }

    // Cancel only the token used for this request.
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
