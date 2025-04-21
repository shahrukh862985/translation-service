<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExportTranslationsController;
use App\Http\Controllers\TranslationsController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'auth'], function () {
  Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

  Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('logout', [AuthController::class, 'logout']);
  });
});

Route::group(['middleware' => 'auth:sanctum'], function () {
  Route::apiResource('translations', TranslationsController::class)->middleware('throttle:50,1');
  Route::get('export/translations/{locale?}', [ExportTranslationsController::class, 'export'])->middleware('throttle:50,1');;
});
