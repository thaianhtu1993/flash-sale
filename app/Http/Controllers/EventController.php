<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Validator;
use Config;
use App;

class EventController extends Controller
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
        $events = Event::all();

        return response()->json([
            'status' => 1,
            'data' => $events
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
                'html_content' => 'required',
                'banner' => 'image',
                'feature_flg' => 'boolean'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $event = new Event();
        $event->fill($request->except('image'));
        $event->image = $this->utilities->uploadImage(Input::file('image'));

        if (Input::file('banner')) {
            $event->banner = $this->utilities->uploadImage(Input::file('banner'));
        }

        $event->admin_created = $this->authService->getUser()->username;
        $event->save();

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
                'title' => 'string|max:255',
                'image' => 'image',
                'html_content' => 'string',
                'banner' => 'image',
                'feature_flg' => 'boolean'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $event = Event::find($id);
        $event->update($request->except('image'));

        if($request->image) {
            $event->image = $this->utilities->replaceImage(Input::file('image'), $event->image);
        }

        if($request->banner) {
            $event->banner = $this->utilities->replaceImage(Input::file('banner'), $event->banner);
        }

        $event->save();

        return Config::get('constant.success.update');
    }

    public function show($id)
    {
        $event = Event::find($id);

        return [
            'status' => 1,
            'data' => $event
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $event = Event::find($id);

        if ($event) {
            unlink(public_path().'/image/'.$event->image);
            $event->delete();
        }

        return Config::get('constant.success.delete');
    }

    public function getEvents(Request $request)
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
        $pagination = $this->filterService->preparePagination(Event::count(), $page, $perPage);
        $events = Event::skip($this->filterService->getSkip($page, $perPage))
            ->take($perPage)
            ->get();

        return [
            'status' => 1,
            'data' => $events,
            'pagination' => $pagination
        ];
    }

    public function getFeatureEvent()
    {
        $event = Event::where('feature_flg', true)->first();

        return [
            'status' => 1,
            'data' => $event
        ];
    }
}
