<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

// Models
use App\Models\User;
use App\Models\UserPhoto;
use App\Models\Wallet;

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
        $username       = trim($request->input('username'));
        $name           = trim($request->input('name'));
        $email          = trim($request->input('email'));
        $phone_number   = trim($request->input('phone_number'));
        $password       = trim($request->input('password'));
        
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

        // cek phone_number sudah terdaftar atau belum
        $cekPhone = User::where('phone_number', $phone_number)->first();
        if ($cekPhone) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor handphone sudah terdaftar.'
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
        $user->created_by   = $username;
        $user->created_at   = date('Y-m-d H:i:s');
        $user->updated_by   = $username;
        $user->updated_at   = date('Y-m-d H:i:s');
        $user->save();

        //buat uuid user photo
        $idUserPhoto = (string) Str::uuid();

        // Disini simpan image default user yang baru mendaftar
        $userPhoto          = new UserPhoto();
        $userPhoto->id      = $idUserPhoto;
        $userPhoto->user_id = $user->id;
        $userPhoto->image   = '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';  
        $userPhoto->created_by   = $username;
        $userPhoto->created_at   = date('Y-m-d H:i:s');
        $userPhoto->updated_by   = $username;
        $userPhoto->updated_at   = date('Y-m-d H:i:s');
        $userPhoto->save();

        //buat uuid user photo
        $idWallet = (string) Str::uuid();
        // Disini Simpan Walletnya
        $wallet = new Wallet();
        $wallet->id = $idWallet;
        $wallet->user_id = $user->id;
        $wallet->amount = 0;
        $wallet->coin_amount = 0;
        $wallet->created_by = $username;
        $wallet->created_at = date(format: 'Y-m-d H:i:s');
        $wallet->updated_by = $username;
        $wallet->updated_at = date('Y-m-d H:i:s');
        $wallet->save();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil registrasi.',
            'data' => $user,
        ],200);
    }
}
