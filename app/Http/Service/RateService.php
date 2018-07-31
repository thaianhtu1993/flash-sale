<?php
namespace App\Http\Service;

use App\ProductRate;
use App\UserRate;
use App;
use App\Comment;

class RateService
{
    protected $rateInfo ;
    /** @var AuthService */
    protected $authService;

    public function __construct()
    {
        $this->authService = App::make('AuthService');
    }

    public function checkAlreadyRateProduct($productId)
    {
        if(!$this->authService->isLogin() || $this->authService->isAnonymousLogin()) {
            return false;
        }

        $check = ProductRate::where('product_id', $productId)
            ->where('user_id', $this->authService->getUser()->id)
            ->first();

        if(!$check) {
            return false;
        }

        return true;
    }

    public function checkAlreadyRateUser($productId, $userId)
    {
        $check = UserRate::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if(!$check) {
            return false;
        }

        return true;
    }

    public function checkCanRateProduct($productId)
    {
        if(!$this->authService->isLogin() || $this->authService->isAnonymousLogin()) {
            $this->setRateInfo([]);
            return false;
        }

        $checkAlreadyRate = Comment::where('user_id', $this->authService->getUser()->id)
            ->where('product_id', $productId)
            ->whereNotNull('rate')
            ->first();
        if($checkAlreadyRate) {
            $this->setRateInfo($checkAlreadyRate);
            return false;
        }

        return true;
    }

    public function getRateInfo()
    {
        return $this->rateInfo;
    }

    public function setRateInfo($rateInfo)
    {
        $this->rateInfo = $rateInfo;

        return true;
    }

}


