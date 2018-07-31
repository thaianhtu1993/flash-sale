<?php

namespace App\Http\Controllers;

use App\Http\Service\Utilities;
use App\Vip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Validator;
use Config;
use App;

class VipController extends Controller
{
    /** @var  Utilities $utilities */
    protected $utilities;

    public function __construct()
    {
        $this->utilities = App::make('Utilities');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vips = Vip::all();
        return response()->json([
            'status' => 1,
            'data' => $vips
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
                'image' => 'image|required'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $vip = new Vip();
        $vip->name = $request->name;
        $vip->image = $this->utilities->uploadImage(Input::file('image'));

        $vip->save();

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
                'image' => 'image'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $vip = Vip::find($id);
        $vip->update($request->except('image'));

        if($request->image) {
            $vip->image = $this->utilities->replaceImage(Input::file('image'), $vip->image);
            $vip->save();
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

}
