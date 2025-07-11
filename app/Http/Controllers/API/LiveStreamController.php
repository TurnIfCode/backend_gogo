<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\LiveStream;


class LiveStreamController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/live-stream/list",
     *     summary="List live streams with status 'live' ordered by started_at descending",
     *     tags={"LiveStream"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of live streams",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="live_id", type="string", example="abc123"),
     *                     @OA\Property(property="username", type="string", example="user1"),
     *                     @OA\Property(property="status", type="string", example="live"),
     *                     @OA\Property(property="started_at", type="string", format="date-time", example="2023-07-10T14:00:00Z"),
     *                     @OA\Property(property="ended_at", type="string", format="date-time", example=null)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function list(Request $request)
    {
        $liveStreams = LiveStream::where('status', 'live')
            ->with('user.userPhoto')
            ->orderBy('started_at', 'desc')
            ->paginate(2);

        return response()->json([
            'success' => true,
            'data' => $liveStreams
        ], 200);
    }
    
    /**
     * @OA\Post(
     *     path="/api/live-stream/start",
     *     summary="Start a live stream by setting live_id",
     *     tags={"LiveStream"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"live_id"},
     *             @OA\Property(property="live_id", type="string", example="abc123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Live stream started successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Live stream started successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The live_id field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Live stream not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Live stream not found for user.")
     *         )
     *     )
     * )
     */
    public function start(Request $request)
    {
        $live_id = trim($request->post("live_id"));

        if ($live_id == "" || empty($live_id)) {
            return response()->json([
                'success' => false,
                'message'=> 'Room live harus dibuat terlebih dahulu.'
            ], 400);
        }

        // Get user from JWT token explicitly
        $user = auth()->user();

        //disini cek jika user sudah login atau belum
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login. Silahkan login terlebih dahulu.',
            ],400);
        }

        // disini insert ke databasenya
        $liveStream = new LiveStream();
        $liveStream->id = (string) Str::uuid();
        $liveStream->user_id = $user->id;
        $liveStream->live_id = $live_id;
        $liveStream->username = $user->username;
        $liveStream->status = 'live';
        $liveStream->started_at = now();
        $liveStream->created_at = now();
        $liveStream->updated_at = now();
        $liveStream->save();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil',
            'data' => $liveStream
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/live-stream/end",
     *     summary="End a live stream by live_id",
     *     tags={"LiveStream"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"live_id"},
     *             @OA\Property(property="live_id", type="string", example="abc123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Live stream ended successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Live stream ended successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The live_id field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Live stream not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Live stream not found for user.")
     *         )
     *     )
     * )
     */
    public function end(Request $request)
    {
        $live_id = trim($request->post("live_id"));

        if ($live_id == "" || empty($live_id)) {
            return response()->json([
                'success' => false,
                'message'=> 'Room live harus diisi.'
            ], 400);
        }

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login. Silahkan login terlebih dahulu.',
            ],400);
        }

        $liveStream = LiveStream::where('user_id', $user->id)
            ->where('live_id', $live_id)
            ->where('status', 'live')
            ->first();

        if (!$liveStream) {
            return response()->json([
                'success' => false,
                'message' => 'Live stream not found for user.'
            ], 404);
        }

        $liveStream->status = 'ended';
        $liveStream->ended_at = now();
        $liveStream->updated_at = now();
        $liveStream->save();

        return response()->json([
            'success' => true,
            'message' => 'Live stream ended successfully',
            'data' => $liveStream
        ], 200);
    }
}
