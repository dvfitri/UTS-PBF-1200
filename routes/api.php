<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;

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

Route::post('login', [UserController::class,'login']);
Route::post('register', [UserController::class,'register']);

Route::prefix('oauth/register')->group(function() {
    Route::get('', [GoogleAuthController::class, 'redirect']);
    Route::get('call-back', [GoogleAuthController::class, 'callbackGoogle']);
});

Route::middleware(['admin.jwt'])->group(function(){
    Route::post('products', [ProductController::class,'create']);
    Route::get('products', [ProductController::class,'read']);
    Route::put('products/{id}', [ProductController::class,'update']);
    Route::delete('products/{id}', [ProductController::class,'delete']);

    Route::post('categories', [CategoryController::class,'create']);
    Route::get('categories', [CategoryController::class,'read']);
    Route::put('categories/{id}', [CategoryController::class,'update']);
    Route::delete('categories/{id}', [CategoryController::class,'delete']);
});