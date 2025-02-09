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
        Route::get('/berkas_cek/{nomor_registrasi}', 'cekBerkas');
    });
    
    Route::prefix('auth')->group(function(){
        Route::controller(AuthController::class)->group(function(){ 
            Route::post('/login', 'login'); 

            // edit profiles
            Route::put('/profile/{id}', 'profile')->middleware('cekToken');
 
            Route::post('/logout', 'logout')->middleware('cekToken');
        });
 

        // admin side d

        //user side
    }); 

    Route::middleware('cekToken')->group(function(){
        Route::middleware('isUser')->group(function(){
            Route::controller(PresensiController::class)->group(function(){
                Route::get('/presensi', 'index');
                Route::post('/presensi', 'presensi');

                Route::get('/pengajuan', 'getPengajuan');
                Route::post('/pengajuan', 'pengajuan');

                Route::get('/laporan', 'getLaporan');
                Route::post('/laporan', 'laporan');

                Route::resource('/laporanakhir', LaporanAkhirController::class);
            });
        });

        Route::middleware('isAdmin')->group(function(){
            Route::resource('users', UserController::class); 
            Route::resource('master', MasterSekolahUniversitasController::class); 

            Route::get('/berkas', [BerkasController::class, 'index']);
            Route::get('/berkas/{id}', [BerkasController::class, 'show']);
            Route::put('/berkas_update_status', [BerkasController::class, 'update_status']);
        });

        Route::middleware('isMentor')->group(function(){
            Route::get('/users_magang_by_mentor', [UserController::class, 'userMagangByMentor']);
            Route::controller(PresensiController::class)->group(function(){
                Route::get('/pengajuan_mentor', 'getPengajuanMentor');
                Route::put('/validasi_pengajuan/{pengajuan_id}', 'validasi_pengajuan');

                Route::get('/laporan_mentor', 'getLaporanMentor');
                Route::put('/validasi_laporan/{laporan_id}', 'validasi_laporan');
            });
        }); 

        Route::middleware('isKepegawaian')->group(function(){
            Route::get('/users_magang', [UserController::class, 'userMagang']);
            Route::get('/users_mentor', [UserController::class, 'userMentor']);
        });
    }); 

});
 