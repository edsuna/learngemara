<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\Tractate;
use Illuminate\Support\Facades\Log;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('tractates/{name}', function ($name) {
    $tractate = Tractate::where('english_name', '=', $name)->firstOrFail();
    return $tractate->toJson();
});

Route::get('tractates', function () {
    $tractates = Tractate::all();
    return $tractates->toJson();
});
