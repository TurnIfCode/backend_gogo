<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bank;
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
     *     description="Create a new user topup transaction with user_id, wallet_id, coin_amount and price",
     *     operationId="topup",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "wallet_id","coin_amount", "price"},
     *             @OA\Property(property="user_id", type="string", example="user_uuid"),
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

        // disini ambil data bank nya
        $bank = Bank::first();
        $bank_name = $bank->bank_name;
        $account_number = (float)$bank->account_number;

        $topup = new UserTopupTransaction();
        $topup->id = (string) Str::uuid();
        $topup->user_id = $user->id;
        $topup->wallet_id = $wallet_id;
        $topup->coin_amount = $coin_amount;
        $topup->price = $price;
        $topup->bank_name = $bank_name;
        $topup->account_number = $account_number;
        $topup->status = 'Proses';
        $topup->created_by = $user->username;
        $topup->created_at = now();
        $topup->updated_by = $user->username;
        $topup->updated_at = now();
        $topup->save();

        return response()->json([
            'success'   => true,
            'message'   => 'Transaksi topup berhasil dibuat',
            'data'      => $topup,
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
            'success'   => true,
            'message'   => 'Daftar transaksi berhasil diambil',
            'data'      => $topups,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/topup/{id}/cancel",
     *     tags={"topup"},
     *     summary="Cancel a topup transaction",
     *     description="Cancel a user topup transaction by ID",
     *     operationId="cancelTopupTransaction",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the topup transaction to cancel",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Topup transaction cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Topup transaction cancelled successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="topup_uuid"),
     *                 @OA\Property(property="status", type="string", example="Cancelled")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Topup transaction not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Topup transaction not found")
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
    public function cancel($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login. Silahkan login terlebih dahulu.',
            ], 401);
        }

        $topup = UserTopupTransaction::find($id);

        if (!$topup) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }

        // Optional: Check if the user owns the transaction or has permission to cancel
        if ($topup->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak berwenang membatalkan transaksi topup ini',
            ], 401);
        }

        if ($topup->status == 'Batal') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi sudah dibatalkan. Tidak dapat diubah.',
            ], 404);
        }

        if ($topup->status == 'Selesai') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi sudah selesai. Tidak dapat dibatalkan.',
            ], 401);
        }

        $topup->status = 'Batal';
        $topup->updated_by = $user->username;
        $topup->updated_at = now();
        $topup->canceled_by = $user->username;
        $topup->canceled_at = now();
        $topup->save();

        return response()->json([
            'success'   => true,
            'message'   => 'Transaksi topup berhasil dibatalkan',
            'data'      => $topup,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/topup/{id}/upload",
     *     tags={"topup"},
     *     summary="Upload bank details and image for a topup transaction",
     *     description="Upload bank_name, account_number, and base64 encoded image for a user topup transaction by ID",
     *     operationId="uploadTopupTransaction",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the topup transaction to upload details for",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bank_name","account_number","image"},
     *             @OA\Property(property="bank_name", type="string", example="Bank ABC"),
     *             @OA\Property(property="account_number", type="string", example="1234567890"),
     *             @OA\Property(property="image", type="string", description="Base64 encoded image string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Topup transaction details uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Topup transaction details uploaded successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="topup_uuid"),
     *                 @OA\Property(property="bank_name", type="string", example="Bank ABC"),
     *                 @OA\Property(property="account_number", type="string", example="1234567890"),
     *                 @OA\Property(property="image", type="string", description="Base64 encoded image string")
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
     *         response=404,
     *         description="Topup transaction not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Topup transaction not found")
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
    public function upload(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login. Silahkan login terlebih dahulu.',
            ], 401);
        }

        $bank_name      = trim($request->post('bank_name'));
        $account_number = trim($request->post('account_number'));
        $image          = trim($request->post('image'));

        if(empty($bank_name) || empty($account_number) || empty($image)) {
            return response()->json([
                'success' => false,
                'message' => 'Bukti transfer harus diisi',
            ], 404);
        }

        $topup = UserTopupTransaction::find($id);

        if (!$topup) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        if ($topup->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak berwenang mengunggah detail untuk transaksi ini',
            ], 401);
        }

        if ($topup->status == 'Batal') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi sudah dibatalkan. Tidak dapat diubah.',
            ], 404);
        }

        if ($topup->status == 'Selesai') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi sudah selesai. Tidak dapat diubah.',
            ], 404);
        }

        $topup->bank_name = $bank_name;
        $topup->account_number = $account_number;
        $topup->image = $image;
        $topup->updated_by = $user->username;
        $topup->updated_at = now();
        $topup->save();

        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi berhasil diunggah',
            'data' => $topup,
        ]);
    }
}
