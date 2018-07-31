<?php

namespace App\Http\Controllers;

use App\Title;
use Illuminate\Http\Request;
use Validator;
use Config;

class TitleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Title::all();

        return response()->json([
            'status' => 1,
            'data' => $categories
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
                'like_count' => 'required|integer',
                'call_count' => 'required|integer',
                'comment_count' => 'required|integer',
                'rate_count' => 'required|integer'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }

        $title = new Title();
        $title->fill($request->input());
        $title->save();

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
                'like_count' => 'integer',
                'call_count' => 'integer',
                'comment_count' => 'integer',
                'rate_count' => 'integer'
            ]
        );

        if($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()
            ],400);
        }
        $title = Title::find($id);
        $title->update($request->input());

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
