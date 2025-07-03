<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

/**
 * @OA\Get(
 *     path="/api/profile",
 *     tags={"profile"},
 *     summary="Get user profile",
 *     description="Get profile of the authenticated user",
 *     operationId="getUserProfile",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="User profile retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="string", example="uuid"),
 *             @OA\Property(property="username", type="string", example="userA"),
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", example="john@example.com"),
 *             @OA\Property(property="phone_number", type="string", example="08123456789")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthorized")
 *         )
 *     )
 * )
 */
class ProfileController extends Controller
{
    public function profile(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login. Silahkan login terlebih dahulu.',
            ],400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil ambil data.',
            'data' => $user,
        ]);
    }
}
