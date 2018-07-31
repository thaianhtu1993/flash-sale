<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Validator;
use Config;
use App;

class UserController extends Controller
{
    /** @var App\Http\Service\FilterService */
    protected $filterService;
    /** @var App\Http\Service\AuthService */
    protected $authService;
    /** @var \App\Http\Service\ProductService */
    protected $productService;

    public function __construct()
    {
        $this->filterService = App::make('FilterService');
        $this->authService = App::make('AuthService');
        $this->productService = App::make('ProductService');
    }

    public function userInfo()
    {
        $user = $this->authService->getUser();

        if($this->authService->isAnonymousLogin()) {
            return [
                'status' => 1,
                'data' => [
                    'username' => $this->authService->getAccessToken()->name,
                    'role' => 'anonymous'
                ]
            ];
        }

        //promote user to new title
        $this->authService->checkPromoteTitle($user);
        $user->load('title','vip');
        $user->getCheckCount();

        if($user->role == 'product') {
            $product = App\Product::find($user->product_id);
            $user->count_like = $this->productService->countLike($product);
            $user->product_status = $product->productStatus;
            $user->average_rate = $product->average_rate;
            $user->number_rate = $product->number_rate;
        }

        return [
            'status' => 1,
            'data' => $user
        ];
    }

    public function index(Request $request)
    {
        $validator = Validator::make(
            $request->query(),
            [
                'page' => 'required|integer',
                'per_page' => 'required|integer'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $page = $request->query('page');
        $perPage = $request->query('per_page');

        $pagination = $this->filterService->preparePagination(User::count(),$page,$perPage);
        $users = User::with('vip')->skip($this->filterService->getSkip($page,$perPage))
            ->take($perPage)
            ->get();

        return [
            'status' => 1,
            'data' => $users,
            'pagination' => $pagination
        ];
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'vip_id' => 'integer|exists:vips,id'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $user = User::find($id);
        $user->vip_id = $request->vip_id;
        $user->save();

        return Config::get('constant.success.update');
    }

    public function getViewHistory()
    {
        $user = $this->authService->getUser();
        $viewHistory = unserialize($user->view_history);
        $viewProduct = [];

        if(!empty($viewHistory)) {
            $viewProduct = App\Product::with('images')->whereIn('id', $viewHistory)->get();
        }

        return [
            'status' => 1,
            'data'   => $viewProduct
        ];
    }
}
