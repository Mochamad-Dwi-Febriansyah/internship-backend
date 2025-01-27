<?php

namespace App\Http\Controllers;

use App\Models\MasterSekolahUniversitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MasterSekolahUniversitasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $master = MasterSekolahUniversitas::get();
            return response()->json([
                'status' => 'success',
                'message' => 'Data master berhasil diambil',
                'data' => $master
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        } 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $masterValidator = Validator::make($request->all(), [
            'nama_sekolah_universitas' => 'required|max:100',
            'jurusan_sekolah' => 'nullable|max:100',
            'fakultas_universitas' => 'nullable|max:100',
            'program_studi_universitas' => 'nullable|max:100',
            'alamat_sekolah_universitas' => 'required|max:255',
            'kabupaten_kota_sekolah_universitas' => 'required|max:100',
            'provinsi_sekolah_universitas' => 'required|max:100',
            'kode_pos_sekolah_universitas' => 'required|max:10',
            'nomor_telp_sekolah_universitas' => 'nullable|regex:/^\+?[\d\s\(\)-]+$/',
            'email_sekolah_universitas' => 'nullable|email',
            
        ]); 
        if($masterValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $masterValidator->errors()
            ], 422);
        } 

        DB::beginTransaction();
        try {
            MasterSekolahUniversitas::firstOrCreate(
                ['email_sekolah_universitas' => $request->email_sekolah_universitas,
                'jurusan_sekolah' => $request->jurusan_sekolah,
                'fakultas_universitas' => $request->fakultas_universitas,
                'program_studi_universitas' => $request->program_studi_universitas,
                ], 
                $request->only([
                    'nama_sekolah_universitas',
                    'alamat_sekolah_universitas',
                    'kabupaten_kota_sekolah_universitas',
                    'provinsi_sekolah_universitas',
                    'kode_pos_sekolah_universitas',
                    'nomor_telp_sekolah_universitas',
                    'email_sekolah_universitas',
                ])
            );
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil menambahkan data master',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $master = MasterSekolahUniversitas::find($id);
            if (!$master) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'master tidak ditemukan'
                ], 404); 
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data master berhasil diambil',
                'data' => $master
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        } 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = MasterSekolahUniversitas::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ], 404);  // Kode status 404, karena data tidak ditemukan
        }
        // dd($user);
        $masterValidator = Validator::make($request->all(), [
            'nama_sekolah_universitas' => 'required|max:100',
            'jurusan_sekolah' => 'nullable|max:100',
            'fakultas_universitas' => 'nullable|max:100',
            'program_studi_universitas' => 'nullable|max:100',
            'alamat_sekolah_universitas' => 'required|max:255',
            'kabupaten_kota_sekolah_universitas' => 'required|max:100',
            'provinsi_sekolah_universitas' => 'required|max:100',
            'kode_pos_sekolah_universitas' => 'required|max:10',
            'nomor_telp_sekolah_universitas' => 'nullable|regex:/^\+?[\d\s\(\)-]+$/',
            'email_sekolah_universitas' => 'nullable|email',
            
        ]); 
        if($masterValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $masterValidator->errors()
            ], 422);
        } 

        DB::beginTransaction();
        try {
            MasterSekolahUniversitas::where([
                ['id', $id],
                ['email_sekolah_universitas', '=', $request->email_sekolah_universitas],
                ['jurusan_sekolah', '=', $request->jurusan_sekolah],
                ['fakultas_universitas', '=', $request->fakultas_universitas],
                ['program_studi_universitas', '=', $request->program_studi_universitas],
            ])->update([
                'nama_sekolah_universitas' => $request->nama_sekolah_universitas,
                'alamat_sekolah_universitas' => $request->alamat_sekolah_universitas,
                'kabupaten_kota_sekolah_universitas' => $request->kabupaten_kota_sekolah_universitas,
                'provinsi_sekolah_universitas' => $request->provinsi_sekolah_universitas,
                'kode_pos_sekolah_universitas' => $request->kode_pos_sekolah_universitas,
                'nomor_telp_sekolah_universitas' => $request->nomor_telp_sekolah_universitas,
            ]);
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengupdate data master', 
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengupdate data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $master = MasterSekolahUniversitas::find($id);
            if (!$master) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'master tidak ditemukan'
                ], 404);
            }
            $master->delete(); 
        return response()->json([
            'status' => 'success',
            'message' => 'Data master berhasil dihapus', 
        ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $th->getMessage()
            ], 500);
        } 
    }
}
