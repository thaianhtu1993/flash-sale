<?php

namespace App\Http\Controllers;

use App\Like;
use App;
use Config;

class LikeController extends Controller
{
    /** @var App\Http\Service\AuthService $authService */
    protected $authService;

    public function __construct()
    {
        $this->authService = App::make('AuthService');
    }

    public function userLikeProduct($productId)
    {
        if(!App\Product::find($productId)) {
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Không tìm thấy product'
                ],
                404
            );
        }

        if ($this->authService->isAnonymousLogin()) {
            return $this->anonymousLike($productId);
        }

        if ($this->authService->isProductLogin()) {
            return response()->json(Config::get('constant.error.role'),401);
        }

        return $this->normalLike($productId);
    }

    private function anonymousLike($productId)
    {
        $checkLike = Like::where('product_id', $productId)
            ->where('anonymous_user', $this->authService->getAccessToken()->name)
            ->first();

        if($checkLike) {
            $checkLike->delete();
            return [
                'status' => 1,
                'message' => 'Bỏ like thành công'
            ];
        }

        $like = new Like([
            'product_id' => $productId,
            'anonymous_user' => $this->authService->getAccessToken()->name
        ]);
        $like->save();

        return [
            'status' => 1,
            'message' => 'Like thành công'
        ];

    }

    private function normalLike($productId)
    {
        $checkLike = Like::where('product_id', $productId)
            ->where('user_id', $this->authService->getUser()->id)
            ->first();

        if($checkLike) {
            $checkLike->delete();
            return [
                'status' => 1,
                'message' => 'Bỏ like thành công'
            ];
        }

        $like = new Like([
            'product_id' => $productId,
            'user_id' => $this->authService->getUser()->id
        ]);
        $like->save();

        return [
            'status' => 1,
            'message' => 'Like thành công'
        ];

    }

}

