<?php
namespace App\Http\Service;

use App\Product;
use App\ProductRate;
use App\Transaction;
use App\User;
use App\UserRate;
use Hash;

class TransactionService
{
    protected $key = 'pleasedonotchangeme';
    protected $transaction;
    public function checkProductAvailable($productId)
    {
        $check = Transaction::where('product_id', $productId)
            ->where('is_agree', true)
            ->where('is_success', false)
            ->first();

        if(!$check) {
            return true;
        }

        return false;
    }

    public function createTransactionCode($transactionId)
    {
        $code = $transactionId.$this->key;

        return hash('sha256',$code);
    }

    public function verifyTransactionCode($transactionId, $transactionCode)
    {
        $transaction = Transaction::find($transactionId);
        if(!$transaction) {
            return false;
        }

        $this->setTransaction($transaction);

        $shaCode = hash('sha256',$transaction->id.$this->key);

        return $shaCode == $transactionCode;
    }

    public function getTransaction()
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction)
    {
        $this->transaction = $transaction;

        return true;
    }

    public function checkTransactionExists($userId, $productId)
    {
        $transaction = Transaction::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('is_accept', false)
            ->where('is_success', false)
            ->first();

        if(!empty($transaction)) {
            $user = User::where('product_id',$productId)->first();
            $transactionData = [
                'transaction_id' => $transaction->id,
                'transaction_code' => $transaction->code,
                'phone_number' => $user->phone_number
            ];
            return $transactionData;
        }

        return false;
    }

    public function getProductTransactionList(Product $product)
    {
        $transactionList = Transaction::with(
            [
                'rate' => function ($query) {
                    $query->with('user');
                },
            ]
        )
            ->where('product_id', $product->id)
            ->get();

        return $transactionList;
    }

}


