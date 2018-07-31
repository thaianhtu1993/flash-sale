<?php

namespace App\Http\Controllers;

use App\Comment;
use Illuminate\Http\Request;
use Validator;
use App;

class CommentController extends Controller
{
    /** @var  $authService App\Http\Service\AuthService */
    protected $authService;
    /** @var $filterService App\Http\Service\FilterService */
    protected $filterService;

    public function __construct()
    {
        $this->authService = App::make('AuthService');
        $this->filterService = App::make('FilterService');
    }

    public function listComment(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'page' => 'required|integer|min:1',
                'per_page' => 'required|integer|min:1',
                'product_id' => 'required|exists:products,id'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        return $this->getComment($request->page, $request->per_page, $request->product_id);


    }

    public function listFeedback(Request $request)
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

        return $this->getComment($request->page, $request->per_page, $this->authService->getUser()->product_id);
    }

    public function adminGetComment(Request $request)
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

        return $this->getComment($request->page, $request->per_page, $request->product_id);
    }

    private function getComment($page, $perPage, $productId)
    {
        $query = Comment::with(
            [
                'replies' => function ($query) {
                    $query->with('user')->orderBy('created_at', 'ASC');
                },
            ]
        )
            ->with('user')
            ->where('product_id', $productId);

        $pagination = $this->filterService->preparePagination($query->count(), $page, $perPage);
        $comments = $query->skip($this->filterService->getSkip($page, $perPage))
            ->take($perPage)
            ->orderBy('created_at', 'DESC')
            ->get();

        return [
            'status' => 1,
            'data' => $comments,
            'pagination' => $pagination
        ];
    }


    public function commentToProduct(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'product_id' => 'integer|exists:products,id',
                'message' => 'required|string',
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $comment = new Comment();

        if(empty($request->product_id) && ($this->authService->isAnonymousLogin() || !$this->authService->isProductLogin())) {
            return response()->json([
                'status' => 0
            ],400);
        }

        $comment->product_id = $request->product_id;
//        if($this->authService->isProductLogin()) {
//            $comment->product_id = $this->authService->getUser()->product_id;
//        }

        $comment->message = $request->message;

        if($this->authService->isAnonymousLogin()) {
            $comment->anonymous_user = $this->authService->getAccessToken()->name;
        }
        else {
            $comment->user_id = $this->authService->getUser()->id;
        }

        $product = App\Product::find($comment->product_id);
        $product->comment_count +=1;

        $product->save();
        $comment->save();

        $comment->user = $this->authService->getUser();

        return [
            'status' => 1,
            'message' => 'Comment thành công',
            'data' => $comment
        ];
    }

    public function deleteComment($id)
    {

    }

    public function replyToComment(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'comment_id' => 'required|integer|exists:comments,id',
                'message' => 'required|string',
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $commentParent = Comment::find($request->comment_id);
        if($commentParent->rate_id != null || $commentParent->comment_parent_id != null) {
            return [
                'status' => 0,
                'message' => 'Không thể reply vào comment này'
            ];
        }

        $comment = new Comment();
        $comment->message = $request->message;
        $comment->comment_parent_id = $request->comment_id;
        $comment->user_id = $this->authService->getUser()->id;
        $comment->save();

        $comment->user = $this->authService->getUser();

        $replies = Comment::with('user')->where('comment_parent_id', $request->comment_id)
            ->get();

        return [
            'status' => 1,
            'message' => 'Comment thành công',
            'data' => $replies
        ];
    }

}
