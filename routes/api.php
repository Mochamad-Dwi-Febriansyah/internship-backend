<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\LaporanAkhirController;
use App\Http\Controllers\MasterSekolahUniversitasController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\UserController; 
use App\Models\LaporanAkhir;
use App\Models\MasterSekolahUniversitas;  
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
            Route::put('/profile/{uuid_user}', 'profile')->middleware('cekToken');
 
            Route::post('/logout', 'logout')->middleware('cekToken');
        });
 

        // admin side 
 
        //user side
    }); 

    Route::middleware('cekToken')->group(function(){
        Route::middleware('isUser')->group(function(){
            Route::controller(PresensiController::class)->group(function(){
                Route::get('/presensi', 'index');
                Route::post('/presensi', 'presensi');
                Route::post('/laporan', 'laporan');

                Route::resource('/laporanakhir', LaporanAkhirController::class);
            });
 
            Route::post('/logout', 'logout')->middleware('cekToken');
 
        });
 

        // admin side 

        //user side
    }); 

    Route::middleware('cekToken')->group(function(){
        Route::middleware('isUser')->group(function(){
            Route::controller(PresensiController::class)->group(function(){
                Route::get('/presensi', 'index');
                Route::post('/presensi', 'presensi');
                Route::post('/laporan', 'laporan');
            });
        });

        Route::middleware('isAdmin')->group(function(){
            Route::resource('users', UserController::class); 
 
            Route::resource('master', MasterSekolahUniversitasController::class);  
        });
    }); 

});
 