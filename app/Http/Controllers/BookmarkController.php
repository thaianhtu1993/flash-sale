<?php

namespace App\Http\Controllers;

use App\Bookmark;
use App\Http\Service\FilterService;
use App\Product;
use App;
use Illuminate\Http\Request;
use Validator;

class BookmarkController extends Controller
{
    /** @var  FilterService $filterService */
    protected $filterService;

    public function __construct()
    {
        $this->filterService = App::make('FilterService');
    }

    public function bookmarkProduct($productId)
    {
        if(!Product::find($productId)) {
            return response()->json(
                [
                    'status' => 0,
                    'message' => 'Không tìm thấy product'
                ],
                404
            );
        }

        $user = \Request::get('token')->user;

        $checkBookmark = Bookmark::where('product_id', $productId)
            ->where('user_id', $user->id)
            ->first();

        if($checkBookmark) {
            $checkBookmark->delete();
            return [
                'status' => 1,
                'message' => 'Bỏ bookmark thành công'
            ];
        }

        $bookmark = new Bookmark([
            'product_id' => $productId,
            'user_id' => $user->id
        ]);
        $bookmark->save();

        return [
            'status' => 1,
            'message' => 'Bookmark thành công'
        ];
    }

    public function getBookmarkList(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'page' => 'required|integer|min:1',
                'per_page' => 'required|integer|min:1',
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $user = \Request::get('token')->user;
        $query = Bookmark::with(
            [
                'product' => function($query) {
                    $query->with('productStatus','priority');
                }
            ]
        )
            ->where('user_id', $user->id);

        $pagination = $this->filterService->preparePagination($query->count(), $request->page, $request->per_page);
        $bookmarkProduct = $query->skip($this->filterService->getSkip($request->page, $request->per_page))
            ->take($request->per_page)
            ->get();

        $products = array();
        foreach($bookmarkProduct as $bookmark) {
            array_push($products, $bookmark->product);
        }

        return [
            'status' => 1,
            'data' => $products,
            'pagination' => $pagination
        ];
    }
}
