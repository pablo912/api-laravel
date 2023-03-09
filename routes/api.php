<?php

use App\Http\Controllers\Invoice\InvoiceController;
use App\Http\Controllers\Padron\PadronController;
use App\Http\Controllers\Plan\PlanController;
use App\Http\Controllers\Search\SearchController;
use App\Http\Controllers\Update\UpdateController;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/





Route::post('/login',  [ UserController::class, 'login']);
Route::post('/sire', [SearchController::class, 'sire'] );


Route::middleware('auth:api')->group( function () {

    Route::prefix('dni')->group(function() {

        Route::get('/{numero}', [SearchController::class, 'dni'] );
        Route::get('/plus/{numero}', [SearchController::class, 'dniplus'] );

    }); 

    Route::prefix('ruc')->group(function() {

        Route::get('/{numero}', [SearchController::class, 'ruc'] );
        Route::get('/plus/{numero}', [SearchController::class, 'rusplus'] );

    }); 
    

});


Route::post('/boleta', [ InvoiceController::class, 'boleta']);
Route::post('/factura', [ InvoiceController::class, 'factura']);

Route::resource('/users', UserController::class );
Route::get('/renew', [UserController::class,'me']);

Route::middleware('auth:api')->group( function () {

    Route::delete('/logout', [UserController::class, 'logout'])->middleware('auth:api');

    Route::get('/padron',  [ PadronController::class, 'download']);
    Route::get('/extract',  [ PadronController::class, 'extract']);
    Route::get('/load',  [ PadronController::class, 'loadtdata']);
    Route::get('/plan', [PlanController::class, 'index']);
});


Route::get('users/verify/{token}', [UserController::class, 'verify'])->name('verify');
Route::get('users/{user}/resend', [UserController::class, 'resend'])->name('resend');


Route::get('/updates', [UpdateController::class,'index']);

Route::post('/recover', [UserController::class, 'recoverpassword']);


Route::post('resetPassword', [UserController::class, 'resetPassword']);

Route::get('/users/remember/{token}', [UserController::class, 'userForRememberToken'])->name('remember');