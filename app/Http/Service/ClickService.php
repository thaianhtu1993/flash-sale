<?php
namespace App\Http\Service;

use Illuminate\Http\Request;
use App\Product;
use App\SmsClick;
use App\CallClick;
use App;

class ClickService
{
    const SMS_CLICK = 1;
    const CALL_CLICK = 2;
    const VIEW_CLICK = 3;

    /** @var AuthService $authService */
    protected $authService;
    /** @var Product $product */
    protected $product;

    public function __construct()
    {
        $this->authService = App::make('AuthService');
    }

    public function saveClick($clickType, $productId)
    {
        $product = Product::find($productId);
        if(!$product) {
            return false;
        }

        $this->setProduct($product);

        switch ($clickType) {
            case self::SMS_CLICK:
                $this->saveSmsClick();
                break;
            case self::CALL_CLICK:
                $this->saveCallClick();
                break;
            case self::VIEW_CLICK:
                $this->saveViewClick();
        }
    }

    private function saveSmsClick()
    {
        $smsClick = SmsClick::where('user_id', $this->authService->getUser()->id)
            ->where('product_id', $this->getProduct()->id)
            ->first();

        if($smsClick) {
            $smsClick->updated_at = date('Y-m-d H:i:s');
            $smsClick->save();
            return true;
        }

        $smsClick = new SmsClick(
            [
                'product_id' => $this->getProduct()->id,
                'user_id' => $this->authService->getUser()->id,
            ]
        );
        $smsClick->save();

        $this->getProduct()->sms_count += 1;
        $this->getProduct()->save();

        return true;
    }

    private function saveViewClick()
    {
        $this->getProduct()->view_count += 1;
        $this->getProduct()->save();

        return true;
    }

    private function saveCallClick()
    {
        $callClick = CallClick::where('user_id', $this->authService->getUser()->id)
            ->where('product_id', $this->getProduct()->id)
            ->first();

        if($callClick) {
            $callClick->updated_at = date('Y-m-d H:i:s');
            $callClick->save();
            return true;
        }

        $callClick = new CallClick(
            [
                'product_id' => $this->getProduct()->id,
                'user_id' => $this->authService->getUser()->id,
            ]
        );
        $callClick->save();

        $this->getProduct()->call_count += 1;
        $this->getProduct()->save();

        return true;
    }

    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}


