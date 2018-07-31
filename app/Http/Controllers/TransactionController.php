<?php

namespace App\Http\Controllers;

use App;
use App\Transaction;
use Illuminate\Http\Request;
use App\UserRate;

class TransactionController extends Controller
{
    /** @var App\Http\Service\TransactionService */
    protected $transactionService;
    /** @var App\Http\Service\RateService */
    protected $rateService;
    /** @var App\Http\Service\AuthService */
    protected $authService;

    public function __construct()
    {
        $this->transactionService = App::make('TransactionService');
        $this->rateService = App::make('RateService');
        $this->authService = App::make('AuthService');
    }

    public function createTransaction(Request $request)
    {
        if(!App\Product::find($request->product_id)) {
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Không tìm thấy product'
                ],
                404
            );
        }

        $user = \Request::get('token')->user;

        $userProduct = App\User::where('product_id', $request->product_id)
            ->first();

        $check = $this->transactionService->checkTransactionExists($user->id, $request->product_id);
        if($check) {
            return [
                'status' => 1,
                'data' => $check,
            ];
        }

        $transaction = new App\Transaction([
            'product_id' => $request->product_id,
            'user_id' => $user->id,
        ]);

        $transaction->save();
        $transactionCode = $this->transactionService->createTransactionCode($transaction->id);

        return [
            'status' => 1,
            'data' => [
                'transaction_id' => $transaction->id,
                'transaction_code' => $transactionCode,
                'product_phone_number' => $userProduct->phone_number
            ],
        ];
    }

    public function checkTransactionStatus(Request $request)
    {
        $check = $this->transactionService->verifyTransactionCode($request->transaction_id, $request->transaction_code);
        if(!$check) {
            return [
                'status' => 0,
                'transaction_status' => 'code_is_not_valid'
            ];
        }

        $transaction = $this->transactionService->getTransaction();
        $userRequest = App\User::find($transaction->user_id);

        if($transaction->is_accept == false) {
            return [
                'status' => 1,
                'transaction_status' => 'not_accept',
                'data' => $userRequest
            ];
        }

        $transactionRate = App\UserRate::where('product_id',$transaction->product_id)
            ->where('user_id',$transaction->user_id)
            ->first();
        $canRate = false;
        if(empty($transactionRate)) {
            $canRate = true;
        }

        if($transaction->is_success == false) {
            return [
                'status' => 1,
                'transaction_status' => 'accepted',
                'data' => [
                    'user' => $userRequest,
                    'can_rate' => $canRate,
                    'rate' => $transactionRate
                ]
            ];
        }


        return [
            'status' => 1,
            'transaction_status' => 'complete',
            'data' => [
                'user' => $userRequest,
                'rate' => $transactionRate,
                'can_rate' => $canRate,
            ]
        ];
    }

    public function acceptTransaction(Request $request)
    {
        $check = $this->transactionService->verifyTransactionCode($request->transaction_id, $request->transaction_code);
        if (!$check) {
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Transaction code không đúng',
                ],
                400
            );
        }

        $transaction = $this->transactionService->getTransaction();
        if($transaction->is_accept || $transaction->is_success) {
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Không thể accept transaction đã được chấp nhận'
                ],
                400
            );
        }

        $product = App\Product::find($transaction->product_id);
        if ($product->passcode != $request->passcode) {
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Pass Code không đúng',
                ],
                400
            );
        }

        $transaction->is_accept = true;
        $transaction->save();

        $transactionRate = App\UserRate::where('product_id',$transaction->product_id)
            ->where('user_id',$transaction->user_id)
            ->first();
        $canRate = false;
        if(empty($transactionRate)) {
            $canRate = true;
        }
        $requestUser = App\User::find($transaction->user_id);

        return [
            'status' => 1,
            'message' => 'Bạn đã đồng ý với yêu cầu giao dịch',
            'data' => [
                'can_rate' => $canRate,
                'rate' => $transactionRate,
                'user' => $requestUser
            ]
        ];
    }

    public function confirmTransactionComplete(Request $request)
    {
        $check = $this->transactionService->verifyTransactionCode($request->transaction_id, $request->transaction_code);
        if (!$check) {
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Transaction code không đúng',
                ],
                400
            );
        }

        $transaction = $this->transactionService->getTransaction();

        if(!empty($request->rate)) {
            if($this->rateService->checkAlreadyRateUser($transaction->product_id, $transaction->user_id)) {
                return [
                    'status' => 0,
                    'message' => 'Bạn đã rate user này'
                ];
            }
            $user = App\User::find($transaction->user_id);
            $user->number_rate += 1;
            $user->total_rate += $request->rate;
            $user->average_rate = round($user->total_rate / $user->number_rate,1) ;

            $userRate = new UserRate();
            $userRate->fill($request->except('transaction_id','transaction_code'));
            $userRate->product_id = $transaction->product_id;
            $userRate->user_id = $transaction->user_id;

            $user->save();
            $userRate->save();
        }

        if($transaction->is_success || !$transaction->is_accept) {
            return [
                'status' => 0,
                'message' => 'Giao dịch chưa được đồng ý hoặc đã thành công'
            ];
        }

        $transaction->is_success = true;
        $transaction->save();

        return [
            'status' => 1,
            'message' => 'Xác nhận giao dịch thành công'
        ];
    }
}
