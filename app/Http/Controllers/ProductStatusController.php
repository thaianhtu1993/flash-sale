<?php

namespace App\Http\Controllers;

use App\Http\Service\Utilities;
use App\ProductStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Validator;
use Config;
use App;

class ProductStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $productStatus = ProductStatus::all();
        return response()->json([
            'status' => 1,
            'data' => $productStatus
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
                'background_color' => 'string|required'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $productStatus = new ProductStatus();
        $productStatus->name = $request->name;
        $productStatus->background_color = $request->background_color;

        $productStatus->save();

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
                'background_color' => 'string'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $productStatus = ProductStatus::find($id);
        $productStatus->update($request->all());

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
        $productStatus = ProductStatus::find($id);

        if ($productStatus) {
            $productStatus->delete();
        }

        return Config::get('constant.success.delete');
    }

}
