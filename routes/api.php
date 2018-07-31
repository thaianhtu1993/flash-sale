<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::options(
    '{all}',
    function () {
        $response = Response::make('');

        return $response;
    }
)->where('all', '.*');


Route::group(
    ['middleware' => 'db.transaction'],
    function () {

        /**
         *
         * DO NOT REQUIRE AUTHENTICATION
         */
        //authentication
        Route::post('register', 'AuthController@register');
        Route::post('login', 'AuthController@login');
        Route::get('anonymous_login', 'AuthController@anonymousLogin');

        //refresh token
        Route::get('refresh_token', 'AuthController@refreshToken');

        Route::get('get_product_category', 'ProductController@getCategoryProduct');
        Route::post('get_products', 'ProductController@getProducts');
        Route::get('get_product/{id}', 'ProductController@getProduct');
        Route::get('view_click/{id}', 'ClickController@viewClick');

        Route::get('get_tags', 'TagController@index');
        Route::get('get_categories', 'CategoryController@getCategories');
        Route::get('get_locations', 'LocationController@getLocations');
        Route::post('list_comment', 'CommentController@listComment');

        Route::post('list_news', 'NewsController@getNews');
        Route::get('news/{id}', 'NewsController@show');
        Route::get('event/{id}', 'EventController@show');
        Route::post('list_event', 'EventController@getEvents');
        Route::get('feature_event', 'EventController@getFeatureEvent');

        Route::get('rule', 'CommonContentController@getRule');
        Route::get('user_guide', 'CommonContentController@getGuide');
        Route::get('display_zalo', 'CommonContentController@isDisplayZalo');

        /**
         * REQUIRE AUTHENTICATION
         */
        Route::group(
            ['middleware' => 'app.api'],
            function () {
                //logout
                Route::get('logout', 'AuthController@logout');
                Route::get('like_product/{id}', 'LikeController@userLikeProduct');
                Route::post('comment_product', 'CommentController@commentToProduct');
                Route::get('user_info', 'UserController@userInfo');

                Route::group(
                    ['middleware' => 'anonymous.denied.access'],
                    function () {
                        Route::post('reply_comment', 'CommentController@replyToComment');
//                        Route::post('comment_rate', 'CommentController@commentToRate');
                    }
                );
                /**
                 * FOR USER
                 */
                Route::group(['middleware' => 'user.access'], function() {
                    Route::get('view_history', 'UserController@getViewHistory');
                    Route::post('list_check', 'ClickController@listClick');

                    //click
                    Route::get('sms_click/{id}', 'ClickController@smsClick');
                    Route::get('call_click/{id}', 'ClickController@callClick');


                    Route::get('bookmark_product/{id}', 'BookmarkController@bookmarkProduct');
                    Route::post('list_bookmark', 'BookmarkController@getBookmarkList');

                    //main page route


//                    Route::post('create_transaction', 'TransactionController@createTransaction');

                    //rate action
                    Route::post('rate_product', 'RateController@userRateProduct');

//                    Route::get('list_rate_product', 'RateController@listProductRate');

                    //report
                    Route::get('report_reason', 'ReportController@index');
                    Route::post('report_product', 'ReportController@reportProduct');
                });

                /**
                 * FOR PRODUCT
                 */
                Route::group(['middleware' => 'product.access'], function() {
                    Route::get('get_product_status', 'ProductStatusController@index');
                    Route::post('update_product_status', 'ProductController@updateProductStatus');
//                    Route::get('get_product_rate', 'ProductController@getProductRate');
                    Route::post('list_feedback', 'CommentController@listFeedback');
//                    Route::post('check_transaction_status', 'TransactionController@checkTransactionStatus');
//                    Route::post('accept_transaction', 'TransactionController@acceptTransaction');
//                    Route::post('confirm_transaction_complete', 'TransactionController@confirmTransactionComplete');
//                    Route::post('rate_user', 'RateController@productRateUser');
//                    Route::get('list_rate_user', 'RateController@listUserRate');
                });

                /**
                 * FOR ADMIN
                 */
                Route::group(['middleware' => 'admin.access'],function() {
                    Route::get('delete_comment/{id}', 'CommentController@deleteComment');
                    Route::post('admin_get_comment', 'CommentController@adminGetComment');
                    Route::get('priorities', 'ProductController@getPriority');
                    Route::post('rule', 'CommonContentController@updateRule');
                    Route::post('user_guide', 'CommonContentController@updateGuide');
                    Route::post('display_zalo', 'CommonContentController@updateDisplayZalo');
                    Route::resource('news', 'NewsController');
                    Route::resource('event', 'EventController');
                    Route::resource('vip', 'VipController');
                    Route::resource('product_status', 'ProductStatusController');
                    Route::resource('category','CategoryController');
                    Route::resource('location','LocationController');
                    Route::resource('tag','TagController');
                    Route::resource('title','TitleController');
                    Route::resource('product','ProductController');
                    Route::resource('user','UserController');
                    Route::post('add_product_images','ProductController@addProductImage');
                });
            }
        );
    }
);

