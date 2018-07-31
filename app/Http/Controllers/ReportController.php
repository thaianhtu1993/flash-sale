<?php

namespace App\Http\Controllers;

use App\ReportProduct;
use App\ReportReason;
use Illuminate\Http\Request;
use App;
use Validator;

class ReportController extends Controller
{
    /** @var  $productService App\Http\Service\ProductService */
    protected $productService;
    /** @var  $authService App\Http\Service\AuthService */
    protected $authService;

    public function __construct()
    {
        $this->productService = App::make('ProductService');
        $this->authService = App::make('AuthService');
    }

    public function index()
    {
        $reasons = ReportReason::all();

        return [
            'status' => 1,
            'data' => $reasons
        ];
    }

    public function reportProduct(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'product_id' => 'required|integer|exists:products,id',
                'reason' => 'required|string',
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        if($this->productService->isReport($request->product_id)) {
            return [
                'status' => 0,
                'message' => 'Bạn đã report product này'
            ];
        }

        $reportProduct = new ReportProduct();
        $reportProduct->fill($request->input());
        $reportProduct->user_id = $this->authService->getUser()->id;

        $reportProduct->save();

        return [
            'status' => 1,
            'message' => 'Report thành công'
        ];
    }



}
