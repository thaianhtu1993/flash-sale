<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Validator;
use App\Like;
use App\ProductRate;
use App\User;
use App;
use Config;


class ProductController extends Controller
{
    /** @var App\Http\Service\FilterService */
    protected $filterService;
    /** @var App\Http\Service\ProductService */
    protected $productService;
    /** @var App\Http\Service\Utilities */
    protected $utilities;
    /** @var App\Http\Service\RateService */
    protected $rateService;
    /** @var  App\Http\Service\AuthService */
    protected $authService;

    public function __construct()
    {
        $this->rateService = App::make('RateService');
        $this->filterService = App::make('FilterService');
        $this->productService = App::make('ProductService');
        $this->utilities = App::make('Utilities');
        $this->authService = App::make('AuthService');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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

        $pagination = $this->filterService->preparePagination(Product::count(),$page,$perPage);
        $products = Product::with('category','location','images','tags','priority','productStatus')->skip($this->filterService->getSkip($page,$perPage))
            ->take($perPage)
            ->get();

        return response()->json([
            'status' => 1,
            'data' => $products,
            'pagination' => $pagination
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'location_id' => 'required|exists:locations,id',
                'email' => 'required|string|max:255|unique:users',
                'passcode' => 'required|string|max:255',
                'priority_id' => 'exists:priorities,id',
                'product_status_id' => 'exists:product_status,id',
                'phone_number' => 'required|string|max:255|unique:users',
                'description' => 'required|string',
                'price_per' => 'required|numeric',
                'tag' => 'required|array',
                'tag.*' => 'exists:tags,id',
                'avatar' => 'required|image',
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $product = new Product();
        $product->fill($request->except(['email','phone_number','avatar','tag']));
        $product->avatar = $this->utilities->uploadImage(Input::file('avatar'));
        $product->save();

        $product->tags()->attach($request->tag);

        $user = new User(
            [
                'email' => $request->email,
                'password' => $request->passcode,
                'phone_number' => $request->phone_number,
                'username' => $product->name,
                'avatar' => $product->avatar,
                'product_id' => $product->id,
            ]
        );
        $user->role = 'product';
        $user->save();

        return Config::get('constant.success.create');
    }

    public function addProductImage(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'product_id' => 'required|exists:products,id',
                'images' => 'array',
                'images.*' => 'image'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $product = Product::find($request->product_id);
        $imageEloquent = [];
        foreach(Input::file('images') as $image) {
            $newImage = new App\Image([
                'path' => $this->utilities->uploadImage($image),
                'product_id' => $product->id
            ]);
            array_push($imageEloquent,$newImage);
        }
        $product->images()->saveMany($imageEloquent);

        return Config::get('constant.success.create');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'string|max:255',
                'category_id' => 'exists:categories,id',
                'location_id' => 'exists:locations,id',
                'priority_id' => 'exists:priorities,id',
                'product_status_id' => 'exists:product_status,id',
                'passcode' => 'string|max:255',
                'description' => 'string',
                'price_per' => 'numeric',
                'tag' => 'array',
                'tag.*' => 'exists:tags,id',
                'avatar' => 'image',
            ]
        );
        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $product = Product::find($id);
        $product->update($request->except(['avatar','tag']));
        $product->tags()->sync($request->tag);
        if($request->avatar) {
            $product->avatar = $this->utilities->replaceImage(Input::file('avatar'),$product->avatar);
            $product->save();
        }

        return Config::get('constant.success.update');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getCategoryProduct()
    {
        $categoryProduct = $this->filterService->categoryProductFilter();

        return [
            'status' => 1,
            'data' => $categoryProduct
        ];
    }


    public function getProducts(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'category_id' => 'required|integer',
                'location_id' => 'integer',
                'min_price' => 'integer',
                'max_price' => 'integer',
                'rate' => 'integer',
                'option_filter' => 'string|in:hot,new',
                'page' => 'required|integer|min:1',
                'per_page' => 'required|integer|min:1',
                'name_search' => 'string',
                'tags' => 'array',
                'tags.*' => 'exists:tags,id'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $products = $this->filterService->productFilter($request);

        return [
            'status' => 1,
            'data' => $products['product'],
            'pagination' => $products['pagination']
        ];
    }

    public function getProduct(Request $request, $id)
    {
        $product = Product::with('images', 'category', 'location', 'tags', 'priority', 'productStatus','user')
            ->where('id', $id)->first();

        if(empty($product)) {
            return response()->json([
                'status' => 0,
                'message' => 'Không tìm thấy product'
            ],400);
        }

        $token = $request->header('app-token');
        if($token) {
            $accessToken = App\AccessToken::where('token', $token)->first();
            if(!empty($accessToken)) {
                if ($accessToken->isExpire()) {
                    if ($accessToken->anonymous) {
                        $accessToken->delete();
                        return response()->json(
                            [
                                'status' => 3,
                                'message' => 'Bạn đã hết phiên sử dụng lần đăng nhập ẩn danh này'
                            ],401
                        );
                    }

                    $accessToken->prepareRefresh();
                    return response()->json(
                        [
                            'status' => 2,
                            'message' => 'Token expire at '.$accessToken->expire_time. '. Please refresh token',
                            'code_refresh' => $accessToken->code_refresh
                        ],
                        200
                    );
                }

                $request->attributes->add(['token' => $accessToken]);
                if ($this->authService->getUser()) {
                    $this->productService->saveViewforUser($this->authService->getUser(), $product->id);
                }
            }
        }

        $product->count_like = $this->productService->countLike($product);

        $product->can_rate = $this->rateService->checkCanRateProduct($product->id);
        $product->rate_info = $this->rateService->getRateInfo();
        $product->is_bookmark = $this->productService->isBookmark($product);
        $product->is_like = $this->productService->isLike($product);
        $product->is_report = $this->productService->isReport($product->id);
        $product->phone_number = $product->user->phone_number;

        $product->sms_content = 'Nhắn tin nè';


        return [
            'status' => 1,
            'data' => $product,
        ];
    }

    public function getProductRate()
    {
        $user = \Request::get('token')->user;
        $rate = ProductRate::with('comments','user')
            ->where('product_id', $user->product_id)
            ->get();

        return [
            'status' => 1,
            'data' => $rate,
        ];
    }

    public function getPriority()
    {
        return [
            'status' => 1,
            'data' => App\Priority::all()
        ];
    }

    public function updateProductStatus(Request $request)
    {
        $product = Product::find($this->authService->getUser()->product_id);

        if(empty($product)) {
            return response()->json([
                'status' => 0,
                'message' => 'Không tìm thấy product'
            ], 400);
        }

        $product->product_status_id = $request->product_status_id;
        $product->save();

        return [
            'status' => 1,
            'message' => 'Thành công'
        ];
    }

}
