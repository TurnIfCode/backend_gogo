<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use HasApiTokens;

use App\Models\User;

class LoginController extends Controller
{
    
    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"login"},
     *     summary="User login",
     *     description="API for user login with username and password",
     *     operationId="loginUser",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="userA"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="token", type="string", example="jwt_token_here"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid username or password")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $username = trim($request->post('username'));
        $password = trim($request->post('password'));

        $user = User::where('username', $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah.'
            ], 401);
        }

        $token = Auth::user()->createToken('api_token')->plainTextToken;


        return response()->json([
            'message' => 'Berhasil login.',
            'user' => $user,
            'token'=> $token,
        ], 200);
    }
}
