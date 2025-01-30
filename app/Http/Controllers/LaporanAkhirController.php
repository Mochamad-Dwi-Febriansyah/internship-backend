<?php

namespace App\Http\Controllers;

use App\Models\LaporanAkhir;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use function App\Providers\logActivity;

class LaporanAkhirController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     // users 
    public function index()
    {
        try {

            $userId = Auth::guard('sanctum')->user()->id;
            $laporanAkhir = LaporanAkhir::where('user_id', $userId)->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $laporanAkhir
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
        $laporanAkhirValidator = Validator::make($request->all(), [
            'berkas_id' => 'required|uuid|exists:berkas,id',
            'master_sekolah_universitas_id' => 'required|uuid|exists:master_sekolah_universitas,id',
            'judul' => 'required',
            'laporan' => 'required',
            'file_laporan' => 'nullable|mimes:pdf,doc,docx|max:2048',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'video' => 'nullable|string',

        ]);
        if ($laporanAkhirValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $laporanAkhirValidator->errors()
            ], 422);
        }


        DB::beginTransaction();
        try {
            $fileLaporanPath = null;
            if ($request->hasFile('file_laporan')) {
                $fileLaporan = $request->file('file_laporan');
                $fileLaporanPath = 'file_laporan/' . $fileLaporan->getClientOriginalName();
                Storage::put($fileLaporanPath, file_get_contents($fileLaporan->getRealPath()));
            };
            $user = Auth::guard('sanctum')->user();

            $laporanAkhir = LaporanAkhir::create([
                'user_id' => $user->id,
                'berkas_id' => $request->berkas_id,
                'master_sekolah_universitas_id' => $request->master_sekolah_universitas_id,
                'judul' => $request->judul,
                'laporan' => $request->laporan,
                'file_laporan' => $fileLaporanPath,
                'foto' => $request->foto,
                'video' => $request->video,
            ]);
            
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'create', 'LaporanAkhir', $laporanAkhir->id, null);


            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil menambahkan data laporan akhir',
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
            $laporanAkhir = LaporanAkhir::find($id);
            if (!$laporanAkhir) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'laporan akhir tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan akhir berhasil diambil',
                'data' => $laporanAkhir
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
        $laporanAkhir = LaporanAkhir::find($id);
        if (!$laporanAkhir) {
            return response()->json([
                'status' => 'error',
                'message' => 'laporan akhir tidak ditemukan'
            ], 404);  // Kode status 404, karena data tidak ditemukan
        }
        $oldData = $laporanAkhir->toArray();
        $laporanAkhirValidator = Validator::make($request->all(), [
            'berkas_id' => 'required|uuid|exists:berkas,id',
            'master_sekolah_universitas_id' => 'required|uuid|exists:master_sekolah_universitas,id',
            'judul' => 'required',
            'laporan' => 'required',
            'file_laporan' => 'nullable|mimes:pdf,doc,docx|max:2048',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'video' => 'nullable|string',

        ]);
        if ($laporanAkhirValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $laporanAkhirValidator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $fileLaporanPath = null;
            if ($request->hasFile('file_laporan')) {
                $fileLaporan = $request->file('file_laporan');
                $fileLaporanPath = 'file_laporan/' . $fileLaporan->getClientOriginalName();
                Storage::put($fileLaporanPath, file_get_contents($fileLaporan->getRealPath()));
            };

            $user = Auth::guard('sanctum')->user(); 

            $laporanAkhir->update([
                'user_id' => $user->id,
                'berkas_id' => $request->berkas_id,
                'master_sekolah_universitas_id' => $request->master_sekolah_universitas_id,
                'judul' => $request->judul,
                'laporan' => $request->laporan,
                'file_laporan' => $fileLaporanPath,
                'foto' => $request->foto,
                'video' => $request->video,
            ]);

            $newData = $laporanAkhir->toArray();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;

            logActivity($user->id, $nama, 'update', 'LaporanAkhir', $laporanAkhir->id, [
                'old' => $oldData,
                'new' => $newData,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengupdate data laporan akhir',
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
            $laporanAkhir = LaporanAkhir::find($id);
            if (!$laporanAkhir) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'laporan akhir tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            $oldData = $laporanAkhir->toArray();
            $laporanAkhir->delete();
            $user = Auth::guard('sanctum')->user();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'delete', 'LaporanAkhir', $laporanAkhir->id, [
                'old' => $oldData,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan akhir berhasil dihapus',  // Mengganti 'diambil' dengan 'dihapus'
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
