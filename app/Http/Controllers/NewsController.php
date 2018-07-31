<?php

namespace App\Http\Controllers;

use App\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Validator;
use Config;
use App;

class NewsController extends Controller
{
    /** @var  App\Http\Service\Utilities $utilities */
    protected $utilities;
    /** @var App\Http\Service\AuthService $authService */
    protected $authService;
    /** @var App\Http\Service\FilterService $filterService  */
    protected $filterService;

    public function __construct()
    {
        $this->authService = App::make('AuthService');
        $this->utilities = App::make('Utilities');
        $this->filterService = App::make('FilterService');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $news = News::all();

        return response()->json([
            'status' => 1,
            'data' => $news
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
                'title' => 'required|string|max:255',
                'image' => 'required|image',
                'html_content' => 'required'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $news = new News();
        $news->fill($request->except('image'));
        $news->image = $this->utilities->uploadImage(Input::file('image'));
        $news->admin_created = $this->authService->getUser()->username;
        $news->save();

        return Config::get('constant.success.create');
    }

    public function show($id)
    {
        $news = News::find($id);

        return [
            'status' => 1,
            'data' => $news
        ];
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
                'title' => 'string|max:255',
                'image' => 'image',
                'html_content' => 'string'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $news = News::find($id);
        $news->update($request->except('image'));

        if($request->image) {
            $news->image = $this->utilities->replaceImage(Input::file('image'), $news->image);
            $news->save();
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
        $news = News::find($id);

        if ($news) {
            unlink(public_path().'/image/'.$news->image);
            $news->delete();
        }

        return Config::get('constant.success.delete');
    }

    public function getNews(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'page' => 'required|numeric',
                'per_page' => 'required|numeric',
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $page = $request->page;
        $perPage = $request->per_page;
        $pagination = $this->filterService->preparePagination(News::count(), $page, $perPage);
        $news = News::skip($this->filterService->getSkip($page, $perPage))
            ->take($perPage)
            ->get();

        return [
            'status' => 1,
            'data' => $news,
            'pagination' => $pagination
        ];
    }
}
