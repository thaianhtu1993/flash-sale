<?php
namespace App\Http\Service;

use App\Bookmark;
use App\Like;
use App\Product;
use App\ReportProduct;
use App;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    /** @var  AuthService */
    protected $authService;

    public function __construct()
    {
        $this->authService = App::make('AuthService');
    }

    public function checkLikeMany(Collection $products, array $userLiked)
    {
        foreach($products as $product) {
            $product->is_like = false;
            if(in_array($product->id, $userLiked)) {
                $product->is_like = true;
            }
        }

        return $products;
    }

    public function isLike(Product $product)
    {
        if(!$this->authService->isLogin()) {
            return false;
        }

        if($this->authService->isAnonymousLogin()) {
            $check = Like::where('product_id', $product->id)
                ->where('anonymous_user', $this->authService->getAccessToken()->name)
                ->first();
        } else {
            $check = Like::where('product_id', $product->id)
                ->where('user_id', $this->authService->getUser()->id)
                ->first();
        }


        if(!$check) {
            return false;
        }

        return true;
    }

    public function isBookmark(Product $product)
    {
        if(!$this->authService->isLogin() || $this->authService->isAnonymousLogin()) {
            return false;
        }

        $check = Bookmark::where('product_id', $product->id)
            ->where('user_id', $this->authService->getUser()->id)
            ->first();

        if(!$check) {
            return false;
        }

        return true;
    }

    public function isReport($productId)
    {
        if(!$this->authService->isLogin() || $this->authService->isAnonymousLogin()) {
            return false;
        }

        $check = ReportProduct::where('product_id', $productId)
            ->where('user_id', $this->authService->getUser()->id)
            ->first();

        if(!empty($check)) {
            return true;
        }

        return false;

    }


    public function countLike(Product $product)
    {
        return Like::where('product_id', $product->id)
            ->count();
    }


    public function suggest(Product $product)
    {
        $products = Product::where('category_id', $product->category_id)
            ->orWhere('location_id', $product->location_id)
            ->orWhere('priority_id', $product->priority_id)
            ->get();

        return $products;
    }

    /**
     * @param App\User $user
     * @param $productId
     */
    public function saveViewForUser(App\User $user, $productId)
    {
        $viewHistory = unserialize($user->view_history);

        if ($viewHistory && in_array($productId, $viewHistory)) {
            return ;
        }

        if (count($viewHistory) == 6) {
            array_shift($viewHistory);
        }

        $viewHistory[] = $productId;

        $user->view_history = serialize($viewHistory);
        $user->save();

    }

}


