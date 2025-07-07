<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\UserTopupTransaction;
use Illuminate\Support\Str;
use function PHPUnit\Framework\isNumeric;

class UserTopupTransactionController extends Controller
{
    /**
     *     @OA\Post(
     *     path="/api/topup",
     *     tags={"topup"},
     *     summary="Create a new topup transaction",
     *     description="Create a new user topup transaction with wallet_id and coin_amount",
     *     operationId="topup",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"wallet_id","coin_amount"},
     *             @OA\Property(property="wallet_id", type="string", example="wallet_uuid"),
     *             @OA\Property(property="coin_amount", type="number", format="float", example=100000.00),
     *             @OA\Property(property="price", type="number", format="float", example=15000.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Topup transaction created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Topup transaction created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="topup_uuid"),
     *                 @OA\Property(property="wallet_id", type="string", example="wallet_uuid"),
     *                 @OA\Property(property="coin_amount", type="number", format="float", example=100000.00),
     *                 @OA\Property(property="price", type="number", format="float", example=15000.00),
     *                 @OA\Property(property="status", type="string", example="Proses"),
     *                 @OA\Property(property="created_by", type="string", example="userA"),
     *                 @OA\Property(property="created_at", type="string", example="2025-07-07 15:50:43")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function topup(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login. Silahkan login terlebih dahulu.',
            ], 401);
        }

        $wallet_id      = trim($request->post('wallet_id'));
        $coin_amount    = trim($request->post('coin_amount'));
        $price          = trim($request->post('price'));

        if (!isNumeric($coin_amount)) {
            return response()->json([
                'success'   => false,
                'message'   => 'Jumlah koin harus berupa angka',
            ],400);
        }

        if ($coin_amount <= 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Jumlah koin harus lebih dari 0.',
            ],400);
        }

        if (!isNumeric($price)) {
            return response()->json([
                'success'   => false,
                'message'   => 'Harga harus berupa angka',
            ],400);
        }

        if ($price <= 0) {
            return response()->json([
                'success'   => false,
                'message'   => 'Harga harus lebih dari 0.',
            ],400);
        }

        $topup = new UserTopupTransaction();
        $topup->id = (string) Str::uuid();
        $topup->wallet_id = $wallet_id;
        $topup->coin_amount = $coin_amount;
        $topup->price = $price;
        $topup->status = 'Proses';
        $topup->created_by = $user->username;
        $topup->created_at = now();
        $topup->updated_by = $user->username;
        $topup->updated_at = now();
        $topup->save();

        return response()->json([
            'success' => true,
            'message' => 'Topup transaction created successfully',
            'data' => $topup,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/topup-list",
     *     tags={"topup"},
     *     summary="Get list of all topup transactions",
     *     description="Retrieve all user topup transactions ordered by updated_at descending",
     *     operationId="listTopupTransactions",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of topup transactions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of topup transactions retrieved successfully"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="topup_uuid"),
     *                     @OA\Property(property="wallet_id", type="string", example="wallet_uuid"),
     *                     @OA\Property(property="coin_amount", type="number", format="float", example=100000.00),
     *                     @OA\Property(property="price", type="number", format="float", example=15000.00),
     *                     @OA\Property(property="status", type="string", example="Proses"),
     *                     @OA\Property(property="created_by", type="string", example="userA"),
     *                     @OA\Property(property="created_at", type="string", example="2025-07-07 15:50:43"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-07-07 15:55:00")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function list()
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login. Silahkan login terlebih dahulu.',
            ], 401);
        }

        $topups = UserTopupTransaction::orderBy('updated_at', 'desc')->get();

        // Round coin_amount and price to 2 decimals
        $topups->transform(function ($topup) {
            if (isset($topup->coin_amount)) {
                $topup->coin_amount = round((float)$topup->coin_amount, 2);
            }
            if (isset($topup->price)) {
                $topup->price = round((float)$topup->price, 2);
            }
            return $topup;
        });

        return response()->json([
            'success' => true,
            'message' => 'List of topup transactions retrieved successfully',
            'data' => $topups,
        ]);
    }
}
