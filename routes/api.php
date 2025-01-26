<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\PresensiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

 
Route::prefix('v1')->group(function () {
    Route::controller(BerkasController::class)->group(function(){
        Route::post('/berkas', 'ajuanBerkas'); 
        Route::get('/berkas/{nomor_registrasi}', 'cekBerkas');
    });
    
    Route::prefix('auth')->group(function(){
        Route::controller(AuthController::class)->group(function(){ 
            Route::post('/login', 'login'); 

            // edit profile
            Route::put('/profile/{uuid_user}', 'profile');

            // edit berkas


            Route::post('/logout', 'logout')->middleware('cekToken');
        });

        Route::controller(PresensiController::class)->group(function(){
            Route::post('/presensi', 'presensi');
        });
    }); 

});
 