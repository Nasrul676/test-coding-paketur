<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Register a new user
     * 
     * Todo: list of roles
     * - super_admin (id: 1)
     * - manager (id: 2)
     * - employee (id: 3)
     */
    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();

        //create user
        $createUser = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role_id' => $validatedData['role_id']
        ]);

        //generate token
        $token = JWTAuth::fromUser($createUser);

        return $this->jsonResponse('User registered successfully', [
            'token' => $token,
            'type' => 'Bearer',
        ], 201);
    }

    /**
     * Login user
     * 
     * superadmin login:
     * default email: superadmin@gmail.com,
     * default password: password
     * employee login:
     * default email: employee@gmail.com,
     * default password: password
     */
    public function login(LoginRequest $request)
    {
        //validate user
        $credentials = $request->only('email', 'password');

        //check if user exists
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status_code' => 401,
                'status_message' => 'Unauthorized',
                'message' => 'Invalid email or password'
            ], 401);
        }

        //generate token
        $token = JWTAuth::fromUser(Auth::user());

        return $this->jsonResponse('User logged in successfully', [
            'token' => $token,
            'type' => 'Bearer',
        ], 200);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        auth()->logout(true);

        return $this->jsonResponse('User logged out successfully', [], 200);
    }

    /**
     * Refresh token user
     */
    public function refresh()
    {
        //refresh token
        $token = auth()->refresh(true, true);
        return $this->jsonResponse('Token refreshed', [
            'token' => $token,
            'type' => 'Bearer',
        ], 200);
    }

    /**
     * create json response structure
     * param string $message, array $data, int $statusCode
     */
    private function jsonResponse(string $message, array $data = [], int $statusCode = 200)
    {
        return response()->json([
            'status_code' => $statusCode,
            'status_message' => $message,
            'data' => $data
        ], $statusCode);
    }
}
