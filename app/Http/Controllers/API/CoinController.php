<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coin;

class CoinController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/coin",
     *     tags={"coin"},
     *     summary="Get list of coins",
     *     description="Get all coins ordered by coin ascending",
     *     operationId="getCoins",
     *     @OA\Response(
     *         response=200,
     *         description="List of coins retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="coin", type="string", example="BTC"),
     *                 @OA\Property(property="name", type="string", example="Bitcoin"),
     *                 @OA\Property(property="price", type="number", format="float", example=12345.67),
     *                 @OA\Property(property="final_price", type="number", format="float", example=12300.45)
     *             )
     *         )
     *     )
     * )
     */
    public function coin()
    {
        $coins = Coin::orderBy('coin_amount', 'asc')->get();

        // Round price and final_price to 2 decimals
        $coins->transform(function ($coin) {
            if (isset($coin->coin_amount)) {
                $coin->coin_amount = round((float)$coin->coin_amount, 2);
            }
            if (isset($coin->price)) {
                $coin->price = round((float)$coin->price, 2);
            }
            if (isset($coin->final_price)) {
                $coin->final_price = round((float)$coin->final_price, 2);
            }
            return $coin;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar koin berhasil diambil',
            'data' => $coins
        ]);
    }
}
