<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PresensiController extends Controller
{
    public function presensi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date|date_format:Y-m-d', 
            'waktu_check_in' => 'required|date_format:H:i:s', 
            'waktu_check_out' => 'required|date_format:H:i:s', 
            'foto_check_in' => 'required|image|mimes:jpeg,jpg,png|max:2048', 
            'foto_check_out' => 'required|image|mimes:jpeg,jpg,png|max:2048', 
            'keterangan' => 'nullable|string|max:255', 
            'latitude' => 'required|decimal:6|between:-90,90', 
            'longitude' => 'required|decimal:6|between:-180,180', 
            'status' => 'required|in:izin,sakit,alpa,hadir' 
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {

            $fotoCheckIn = $request->file('foto_check_in');
            $fotoCheckInPath = 'presensi/' . $fotoCheckIn->getClientOriginalName();
            Storage::put($fotoCheckInPath, file_get_contents($fotoCheckIn->getRealPath()));
    
            $fotoCheckOut = $request->file('foto_check_out');
            $fotoCheckOutPath = 'presensi/' . $fotoCheckOut->getClientOriginalName();
            Storage::put($fotoCheckOutPath, file_get_contents($fotoCheckOut->getRealPath()));

            $presensi = Presensi::create([
                'tanggal' => $request->tanggal,
                'waktu_check_in' => $request->waktu_check_in,
                'waktu_check_out' => $request->waktu_check_out,
                'foto_check_in' => $fotoCheckInPath,
                'foto_check_out' => $fotoCheckOutPath,
                'keterangan' => $request->keterangan,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => $request->status
            ]);
    
          
    
            DB::commit();
            return response()->json([
                'message' => 'Presensi berhasil',
                'data' => $presensi
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan presensi',
                'error' => $th->getMessage()
            ], 500);
        }
      
    }
}
