<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

class RegisterController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"register"},
     *     summary="Register User",
     *     description="API for user register with username, name, email, phone_number, password",
     *     operationId="registerUser",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","name","email","phone_number","password"},
     *             @OA\Property(property="username", type="string", example="userA"),
     *             @OA\Property(property="name", type="string", example="Jhon Doe"),
     *             @OA\Property(property="email", type="string", example="jhon@example.com"),
     *             @OA\Property(property="phone_number", type="string", example="081xxxxxx"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Register successful"),
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
    public function register(Request $request)
    {
        $username       = trim($request->post('username'));
        $name           = trim($request->post('name'));
        $email          = trim($request->post('email'));
        $phone_number   = trim($request->post('phone_number'));
        $password       = trim($request->post('password'));
        
        // Cek username
        if (strlen($username) < 4) {
            return response()->json([
                'success' => false,
                'message' => 'Username minimal 4 karakter.'
            ],400);
        }

        $cekUsername = User::where('username', $username)->first();
        if ($cekUsername) {
            return response()->json([
                'success' => false,
                'message' => 'Username sudah digunakan.'
            ],400);
        }

        // cek email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Format email tidak valid.'
            ], 400);
        }

        // cek email uniqueness
        $cekEmail = User::where('email', $email)->first();
        if ($cekEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah digunakan.'
            ], 400);
        }

        // cek phone_number numeric and length
        if (!ctype_digit($phone_number) || strlen($phone_number) < 11) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor handphone harus berupa angka dan minimal 11 digit.'
            ], 400);
        }

        //cek password
        if (strlen($password) < 8) {
            return response()->json([
                'success' => false,
                'message' => 'Password minimal 8 karakter.'
            ],400);
        }

        $id = (string) Str::uuid();

        // Simpan data user
        $user               = new User();
        $user->id           = $id;
        $user->username     = $username;
        $user->name         = $name;
        $user->email        = $email;
        $user->phone_number = $phone_number;
        $user->is_host      = false;
        $user->password     = Hash::make($password);
        $user->save();
        

        return response()->json([
            'success' => true,
            'message' => 'Berhasil registrasi.',
            'data' => $user,
        ],200);
    }
}
