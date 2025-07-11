<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Wallet;

class WalletController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/wallet/{user_id}",
     *     tags={"wallet"},
     *     summary="Get wallet by user id",
     *     description="Get wallet details of the user by user id",
     *     operationId="getWalletByUserId",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wallet retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="wallet_id"),
     *             @OA\Property(property="user_id", type="string", example="user_id"),
     *             @OA\Property(property="amount", type="number", format="float", example=1000.50),
     *             @OA\Property(property="coin_amount", type="number", format="float", example=1000.50),
     *             @OA\Property(property="created_at", type="string", example="2025-07-04 14:22:22"),
     *             @OA\Property(property="updated_at", type="string", example="2025-07-04 14:22:22")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Wallet not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Wallet not found")
     *         )
     *     )
     * )
     */
    public function getWalletByUserId(Request $request, $user_id) {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login. Silahkan login terlebih dahulu.',
            ], 400);
        }

        $wallet = Wallet::where('user_id', $user_id)->first();

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet tidak ditemukan.',
            ], 404);
        }

        $walletData = $wallet->toArray();
        $walletData['amount'] = round((float)$walletData['amount'], 2);
        $walletData['coin_amount'] = round((float)$walletData['coin_amount'], 2);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil',
            'data' => $walletData
        ]);
    }
}
