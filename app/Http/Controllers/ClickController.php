<?php

namespace App\Http\Controllers;

use App;
use App\Http\Service\ClickService;
use App\Http\Service\AuthService;
use App\Http\Service\FilterService;
use Validator;
use Illuminate\Http\Request;

class ClickController extends Controller
{
    /** @var ClickService $clickService */
    protected $clickService;
    /** @var  AuthService $authService */
    protected $authService;
    /** @var  FilterService $filterService */
    protected $filterService;

    public function __construct()
    {
        $this->clickService = App::make('ClickService');
        $this->filterService = App::make('FilterService');
        $this->authService = App::make('AuthService');
    }

    public function smsClick($productId)
    {
        $this->clickService->saveClick(ClickService::SMS_CLICK, $productId);

        return [
            'status' => 1,
        ];
    }

    public function callClick($productId)
    {
        $this->clickService->saveClick(ClickService::CALL_CLICK, $productId);

        return [
            'status' => 1,
        ];
    }

    public function viewClick($productId)
    {
        $this->clickService->saveClick(ClickService::VIEW_CLICK, $productId);

        return [
            'status' => 1
        ];
    }

    public function listClick(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'page' => 'required|integer',
                'per_page' => 'required|integer',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => 0,
                    'message' => $validator->errors(),
                ],
                400
            );
        }

        $query = App\Product::with('productStatus', 'priority')
            ->whereHas(
                'SmsClick',
                function ($query) {
                    $query->where('user_id', $this->authService->getUser()->id);
                }
            )->whereHas(
                'CallClick',
                function ($query) {
                    $query->where('user_id', $this->authService->getUser()->id);
                }
            );

        $pagination = $this->filterService->preparePagination($query->count(), $request->page, $request->per_page);
        $products = $query->skip($this->filterService->getSkip($request->page, $request->per_page))
            ->take($request->per_page)
            ->get();

        return [
            'status' => 1,
            'data' => $products,
            'pagination' => $pagination,
        ];
    }
}
