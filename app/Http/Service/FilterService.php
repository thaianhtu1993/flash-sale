<?php
namespace App\Http\Service;

use App\Category;
use App\Priority;
use App\Product;
use DB;
use Illuminate\Http\Request;

class FilterService
{
    public function getSkip($page, $perPage)
    {
        if ($page == 1) {
            return 0;
        }

        return ($page - 1) * $perPage;
    }

    public function preparePagination($totalResult, $page, $perPage)
    {
        $totalPage = ceil($totalResult / $perPage);

        $prevPage = null;
        if ($page > 1) {
            $prevPage = $page - 1;
        }

        $nextPage = null;
        if ($totalPage >= $page + 1) {
            $nextPage = $page + 1;
        }


        return [
            'total_result' => $totalResult,
            'total_page' => $totalPage,
            'current_page' => intval($page),
            'next_page' => $nextPage,
            'prev_page' => $prevPage,
            'per_page' => intval($perPage),
        ];
    }

    public function categoryProductFilter()
    {
        //TODO need to optimize this one
        $categoryProduct = Category::get();

        foreach($categoryProduct as $category) {
            $category->product;
        }

        return $categoryProduct;
    }

    public function productFilter(Request $request)
    {
        $page = $request->page;
        $perPage = $request->per_page;
        $query = Product::with('productStatus','priority');

        if ($request->min_price) {
            $query->where('price_per', '>=', $request->min_price);
        }
        if ($request->max_price && $request->max_price > 0) {
            $query->where('price_per', '<=', $request->max_price);
        }

        if ($request->category_id && $request->category_id != 0) {
            $query->where('category_id', $request->category_id);
        }

        if($request->location_id && $request->location_id != 0) {
            $query->where('location_id', $request->location_id);
        }

        if($request->tags) {
            $tagIds = $request->tags;
            $query->whereHas('tags', function($q) use($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        }

        if($request->rate) {
            $query->where('average_rate', '>=', $request->rate);
        }

        if ($request->option_filter == 'hot') {
            $query->where('priority_id', Priority::HOT);
        }
        elseif($request->option_filter == 'new') {
            $query->where('priority_id', Priority::BRAND_NEW);
        }

        if($request->name_search) {
            $nameSearch = $request->name_search;
            $query->where('name','like', "%$nameSearch%");
        }

        $pagination = $this->preparePagination($query->count(),$page,$perPage);

        $products = $query->skip($this->getSkip($page,$perPage))
            ->take($perPage)
            ->get();

        return [
            'product' => $products,
            'pagination' => $pagination
        ];
    }


}


