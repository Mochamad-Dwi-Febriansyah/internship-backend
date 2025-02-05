<?php

namespace App\Http\Controllers;

use App\Models\LaporanAkhir;
use App\Models\LaporanHarian;
use App\Models\Pengajuan;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

use function App\Providers\logActivity;

class PresensiController extends Controller
{
    public function index(){
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
            $presensi = Cache::remember("presensi_user_{$userId}", 600, function () use ($userId) {
                return Presensi::where('user_id', $userId)->with('laporanHarians')->get();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $presensi
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }  
    }
    public function presensi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'berkas_id' => 'required|uuid|exists:berkas,id',
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
                'status' => 'error',
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

            $user = JWTAuth::parseToken()->authenticate();

            $presensi = Presensi::create([
                'user_id' => $user->id,
                'berkas_id' =>  $request->berkas_id,
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
    
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'create', 'Presensi', $presensi->id, null);
     
              Cache::forget("presensi_user_{$user->id}");  
               
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Presensi berhasil',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan presensi',
                'error' => $th->getMessage()
            ], 500);
        }
      
    }

    public function getPengajuan(){
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
            $pengajuan  = Pengajuan::where('user_id', $userId)->get(); 

            return response()->json([
                'status' => 'success',
                'message' => 'Data pengajuan berhasil diambil',
                'data' => $pengajuan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }   
    }


    
    public function pengajuan(Request $request){
        
        $validator = Validator::make($request->all(), [ 
            'keterangan' => 'nullable|string|max:255',  
            'tanggal' => 'date',   
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            $user = JWTAuth::parseToken()->authenticate();
            Pengajuan::create([
                'user_id' => $user->id,
                'keterangan' => $request->keterangan,
                'tanggal' => $request->tanggal,
            ]);

            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'create', 'Pengajuan', null, null);
            Cache::forget("presensi_user_{$user->id}");  
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan berhasil disimpan',
            ], 201);
            
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan pengajuan',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function getLaporan(){
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
            $laporan  = LaporanHarian::where('user_id', $userId)->get(); 

            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan berhasil diambil',
                'data' => $laporan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }   
    }



    public function laporan(Request $request){
        $validator = Validator::make($request->all(), [
            'presensi_id' => 'required|uuid|exists:presensis,id',
            'judul' => 'required', 
            'laporan' => 'required', 
            'foto' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',  
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try { 
            $fotoLaporan = $request->file('foto');
            $fotoLaporanPath = 'laporan/' . $fotoLaporan->getClientOriginalName();
            Storage::put($fotoLaporanPath, file_get_contents($fotoLaporan->getRealPath()));

            $user = JWTAuth::parseToken()->authenticate();

            $laporan = LaporanHarian::create([
                'user_id' => $user->id,
                'presensi_id' => $request->presensi_id,
                'judul' => $request->judul, 
                'laporan' => $request->laporan, 
                'foto' => $fotoLaporanPath,  
            ]);
    
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'create', 'LaporanHarian', $laporan->id, null);
            Cache::forget("presensi_user_{$user->id}");  
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Laporan berhasil disimpan',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan laporan',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    //mentor

    public function getPengajuanMentor(){
        try {
            $mentorId = JWTAuth::parseToken()->authenticate()->id;
            // $pengajuan  = Pengajuan::where('user_id', $userId)->get(); 
           
            $pengajuan = Pengajuan::whereIn('user_id', function ($query) use ($mentorId) {
                $query->select('user_id')
                      ->from('berkas')
                      ->where('mentor_id', $mentorId);
            })->get();
            
            // dd($pengajuan);
            return response()->json([
                'status' => 'success',
                'message' => 'Data pengajuan berhasil diambil',
                'data' => $pengajuan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }   
    }

    public function getLaporanMentor(){
        try {
            $mentorId = JWTAuth::parseToken()->authenticate()->id; 
           
            $laporan = LaporanHarian::whereIn('user_id', function ($query) use ($mentorId) {
                $query->select('user_id')
                      ->from('berkas')
                      ->where('mentor_id', $mentorId);
            })->get();
             
            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan berhasil diambil',
                'data' => $laporan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }   
    }

    public function validasi_pengajuan(Request $request, $id){ 
        $pengajuan = Pengajuan::find($id);
        if (!$pengajuan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengajuan tidak ditemukan'
            ], 404);  // Kode status 404, karena data tidak ditemukan
        }

        $oldData = $pengajuan->toArray();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,pending,rejected' 
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            

            $pengajuan->update([
                'status' => $request->status,
            ]);
            $newData = $pengajuan->toArray(); 

            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang; 
            logActivity($user->id, $nama, 'update', 'Pengajuan', $pengajuan->id, [
                'old' => $oldData,
                'new' => $newData,
            ]);
            Cache::forget("presensi_user_{$user->id}");  
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan berhasil divalidasi',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat validasi pengajuan',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function validasi_laporan(Request $request, $id){ 
        $laporan = LaporanHarian::find($id);
        if (!$laporan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Laporan tidak ditemukan'
            ], 404);  // Kode status 404, karena data tidak ditemukan
        }

        $oldData = $laporan->toArray();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,pending,rejected' 
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {

            $laporan->update([
                'status' => $request->status,
            ]);
            $newData = $laporan->toArray(); 

            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang; 
            logActivity($user->id, $nama, 'update', 'LaporanHarian', $laporan->id, [
                'old' => $oldData,
                'new' => $newData,
            ]);
            Cache::forget("presensi_user_{$user->id}");  
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Laporan berhasil divalidasi',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat validasi laporan',
                'error' => $th->getMessage()
            ], 500);
        }
    }

}
