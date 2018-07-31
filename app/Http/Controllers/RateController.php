<?php

namespace App\Http\Controllers;

use App\Http\Service\AuthService;
use App\Http\Service\RateService;
use App\Http\Service\TransactionService;
use App\Product;
use App\ProductRate;
use App\Comment;
use App\User;
use App\UserRate;
use DB;
use Illuminate\Http\Request;
use Validator;

class RateController extends Controller
{
    /** @var TransactionService */
    protected $transactionService;
    /** @var RateService */
    protected $rateService;
    /** @var  AuthService */
    protected $authService;

    public function __construct()
    {
        $this->transactionService = \App::make('TransactionService');
        $this->rateService = \App::make('RateService');
        $this->authService = \App::make('AuthService');
    }

    public function userRateProduct(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'product_id' => 'required|integer|exists:products,id',
                'rate' => 'required|numeric|max:5',
                'message' => 'required|string',
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $user = $this->authService->getUser();
        $product = Product::find($request->product_id);

        $canRate = $this->rateService->checkCanRateProduct($product->id);
        if(!$canRate) {
            return [
                'status' => 0,
                'message' => 'Không thể rate bạn đã rate product này rồi'
            ];
        }

        $product->number_rate += 1;
        $product->total_rate += $request->rate;
        $product->average_rate = round($product->total_rate / $product->number_rate,1) ;

        $commentRate = new Comment();
        $commentRate->fill($request->input());
        $commentRate->user_id = $user->id;

        $commentRate->save();
        $product->save();


        return [
            'status' => 1,
            'message' => 'Rate thành công'
        ];
    }

    public function productRateUser(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'user_id' => 'required',
                'rate' => 'required|numeric|max:5',
                'message' => 'required|string',
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $userProduct = $this->authService->getUser();
        $user = User::find($request->user_id);

        if($this->rateService->checkAlreadyRateUser($userProduct->product_id, $request->user_id)) {
            return [
                'status' => 0,
                'message' => 'Bạn đã rate user này'
            ];
        }


        $user->number_rate += 1;
        $user->total_rate += $request->rate;
        $user->average_rate = round($user->total_rate / $user->number_rate,1) ;

        $userRate = new UserRate();
        $userRate->fill($request->input());
        $userRate->product_id = $userProduct->product_id;

        $user->save();
        $userRate->save();

        return [
            'status' => 1,
            'message' => 'Rate thành công'
        ];
    }

}
