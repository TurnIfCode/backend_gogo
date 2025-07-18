<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\UserPhoto;

class ProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/profile/{id}",
     *     tags={"profile"},
     *     summary="Get user profile by id",
     *     description="Get profile of the user by user id",
     *     operationId="getUserProfileById",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="uuid"),
     *             @OA\Property(property="username", type="string", example="userA"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="phone_number", type="string", example="08123456789"),
     *             @OA\Property(property="user_photo", type="object",
     *                 @OA\Property(property="id", type="string", example="photo_id"),
     *                 @OA\Property(property="images", type="string", example="base64imagestring"),
     *                 @OA\Property(property="created_by", type="string", example="userA"),
     *                 @OA\Property(property="created_at", type="string", example="2025/07/03 01:46:43"),
     *                 @OA\Property(property="updated_by", type="string", example="userA"),
     *                 @OA\Property(property="updated_at", type="string", example="2025/07/03 01:46:43")
     *             )
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
     * )
    */
    public function profile(Request $request, $id) {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login. Silahkan login terlebih dahulu.',
            ],400);
        }

        $dataUser = User::with('userPhoto')->find($id);

        if (!$dataUser) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil',
            'data' => $dataUser
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/change-photo",
     *     tags={"profile"},
     *     summary="Change user photo",
     *     description="Upload or update user photo as base64 string",
     *     operationId="changeUserPhoto",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         request="body",
     *         @OA\JsonContent(
     *             required={"image"},
     *             @OA\Property(property="image", type="string", example="base64imagestring")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User photo updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Photo updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="photo_id"),
     *                 @OA\Property(property="images", type="string", example="base64imagestring"),
     *                 @OA\Property(property="created_by", type="string", example="userA"),
     *                 @OA\Property(property="created_at", type="string", example="2025/07/03 01:46:43"),
     *                 @OA\Property(property="updated_by", type="string", example="userA"),
     *                 @OA\Property(property="updated_at", type="string", example="2025/07/03 01:46:43")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Image is required")
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
    public function changePhoto(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login. Silahkan login terlebih dahulu.',
            ],400);
        }

        // Disini ambil data user foto berdasarkan user_id
        $dataUserPhoto = UserPhoto::where('user_id', $user->id)->first();

        $userPhotoId = $dataUserPhoto->id;

        $image = trim($request->post('image'));

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Gambar harus diisi',
            ],400);
        }

        // Check base64 string length to prevent memory exhaustion
        $maxBase64Length = (5 * 1024 * 1024) * 4 / 3; // approx base64 length for 5MB binary
        if (strlen($image) > $maxBase64Length) {
            return response()->json([
                'success' => false,
                'message' => 'Ukuran gambar terlalu besar, maksimal 5MB',
            ], 400);
        }

        // Decode base64 image
        $imageData = explode(',', $image);
        $decodedImage = base64_decode(end($imageData));

        // Resize image to max 5MB if larger than 128MB
        $maxSizeLarge = 5 * 1024 * 1024; // 5MB in bytes
        if (strlen($decodedImage) > 134217728) { // 128MB in bytes
            // Create image resource from decoded data
            $imageResource = imagecreatefromstring($decodedImage);
            if ($imageResource === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gambar tidak valid',
                ], 400);
            }

            // Calculate scale ratio
            $scale = sqrt($maxSizeLarge / strlen($decodedImage));
            $width = imagesx($imageResource);
            $height = imagesy($imageResource);
            $newWidth = (int)($width * $scale);
            $newHeight = (int)($height * $scale);

            // Create new resized image
            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resizedImage, $imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Capture output buffer to get resized image data
            ob_start();
            imagejpeg($resizedImage, null, 85); // quality 85
            $resizedImageData = ob_get_clean();

            imagedestroy($imageResource);
            imagedestroy($resizedImage);

            // Re-encode to base64
            $base64Prefix = explode(',', $image)[0];
            $image = $base64Prefix . ',' . base64_encode($resizedImageData);
        }


        $userPhoto = UserPhoto::find($userPhotoId);
        $userPhoto->image = $image;
        $userPhoto->updated_by = $user->username;
        $userPhoto->updated_at = date('Y-m-d H:i:s');
        $userPhoto->save();

        // disini ambil kembali data usernya
        $getDataUser = User::with('userPhoto')->find($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil diupdate',
                'data' => $getDataUser,
            ]);
    }
}
