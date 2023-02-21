<?php

use App\Http\Controllers\Padron\PadronController;
use App\Http\Controllers\Plan\PlanController;
use App\Http\Controllers\Search\SearchController;
use App\Http\Controllers\User\UserController;
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




Route::post('/login',  [ UserController::class, 'login']);
Route::post('/sire', [SearchController::class, 'sire'] );


Route::middleware('fox')->group( function () {

    Route::prefix('dni')->group(function() {

        Route::get('/{numero}', [SearchController::class, 'dni'] );
        Route::get('/plus/{numero}', [SearchController::class, 'dniplus'] );

    }); 

    Route::prefix('ruc')->group(function() {

        Route::get('/{numero}', [SearchController::class, 'ruc'] );
        Route::get('/plus/{numero}', [SearchController::class, 'rusplus'] );
    }); 
    

});




Route::middleware('auth:api')->group( function () {

    Route::delete('/logout', [UserController::class, 'logout'])->middleware('auth:api');
    Route::get('/renew', [UserController::class,'me']);
    Route::resource('/users', UserController::class );
    Route::get('/padron',  [ PadronController::class, 'download']);
    Route::get('/extract',  [ PadronController::class, 'extract']);
    Route::get('/load',  [ PadronController::class, 'loadtdata']);
    Route::get('/plan', [PlanController::class, 'index']);
});


