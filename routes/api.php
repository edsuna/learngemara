<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Tractate;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
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
