<?php

use App\Jobs\Logger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'App\Http\Controllers'], function () use ($router) {
    Route::group(['prefix' => 'songs'], function () use ($router) {
        $router->get('/', 'SongController@index');
        $router->post('/', 'SongController@store');
        $router->get('/show/{id}', 'SongController@show');
        $router->get('/info', 'SongController@getVideoInfo');
        $router->get('/download', 'SongController@download');

    });

    Route::get('/greeting', function () {
        Logger::dispatchAfterResponse();
        Logger::dispatch();
        Logger::dispatch()->onQueue('secondary');

        return response("FIN");
    });

});
