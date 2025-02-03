<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use App\Models\MasterSekolahUniversitas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

use function App\Providers\logActivity;

class BerkasController extends Controller
{
    public function ajuanBerkas(Request $request)
    {
        $userValidator = Validator::make($request->all(), [
            'nisn_npm_nim' => 'max:20',
            'tanggal_lahir' => 'required|date',
            'nama_depan' => 'required',
            'nama_belakang' => 'nullable',
            'jenis_kelamin' => 'required|in:male,female',
            'nomor_hp' => 'required|unique:users,nomor_hp|regex:/^\+?[\d\s\(\)-]+$/',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable',
            'alamat' => 'required',
            'kabupaten_kota' => 'required',
            'provinsi' => 'required',
            'kode_pos' => 'required',
            'role' => 'required|in:admin,user',  
            
        ]); 

        if($userValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $userValidator->errors()
            ], 422);
        } 

        $sekolahValidator = Validator::make($request->all(), [
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

        if ($sekolahValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal pada data Master Sekolah/Universitas',
                'errors' => $sekolahValidator->errors()
            ], 422);
        };

        $berkasValidator = Validator::make($request->all(), [
            'foto_identitas' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'surat_permohonan' => 'required|mimes:pdf,doc,docx|max:2048',
            'cv_riwayat_hidup' => 'required|mimes:pdf,doc,docx|max:2048',
            'surat_diterima' => 'nullable|mimes:pdf,doc,docx|max:2048',
            'status_berkas' => 'required|in:terima,pending,tolak',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
        ]);

        if ($berkasValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal pada berkas',
                'errors' => $berkasValidator->errors()
            ], 422);
        }
        // dd($request->all());
        DB::beginTransaction();

        try {
            
            $masterSekolah = MasterSekolahUniversitas::firstOrCreate(
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
            logActivity(null, null, 'create', 'MasterSekolahUniversitas', $masterSekolah->id, null);
    
            $user = User::create([
                'nisn_npm_nim' => $request->nisn_npm_nim,
                'tanggal_lahir' => $request->tanggal_lahir,
                'nama_depan' => $request->nama_depan,
                'nama_belakang' => $request->nama_belakang,
                'jenis_kelamin' => $request->jenis_kelamin,
                'nomor_hp' => $request->nomor_hp,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'alamat' => $request->alamat,
                'kabupaten_kota' => $request->kabupaten_kota,
                'provinsi' => $request->provinsi,
                'kode_pos' => $request->kode_pos,
                'role' => $request->role
            ]); 
            $token = JWTAuth::fromUser($user); 
            
            logActivity(null, null, 'create', 'User', $user->id, null);
            // dd($user);

             // Menyimpan file berkas ke storage menggunakan Storage::put
            $fotoIdentitas = $request->file('foto_identitas');
            $fotoIdentitasPath = 'berkas/' . $fotoIdentitas->getClientOriginalName();
            Storage::put($fotoIdentitasPath, file_get_contents($fotoIdentitas->getRealPath()));

            $suratPermohonan = $request->file('surat_permohonan');
            $suratPermohonanPath = 'berkas/' . $suratPermohonan->getClientOriginalName();
            Storage::put($suratPermohonanPath, file_get_contents($suratPermohonan->getRealPath()));

            $cvRiwayatHidup = $request->file('cv_riwayat_hidup');
            $cvRiwayatHidupPath = 'berkas/' . $cvRiwayatHidup->getClientOriginalName();
            Storage::put($cvRiwayatHidupPath, file_get_contents($cvRiwayatHidup->getRealPath()));

            $suratDiterimaPath = null;
            if ($request->hasFile('surat_diterima')) {
                $suratDiterima = $request->file('surat_diterima');
                $suratDiterimaPath = 'berkas/' . $suratDiterima->getClientOriginalName();
                Storage::put($suratDiterimaPath, file_get_contents($suratDiterima->getRealPath()));
            }
 
             // Menyimpan data Berkas
             $berkas = Berkas::create([
                 'user_id' => $user->id,
                 'master_sekolah_universitas_id' => $masterSekolah->id,
                 'foto_identitas' => $fotoIdentitasPath,
                 'surat_permohonan' => $suratPermohonanPath,
                 'cv_riwayat_hidup' => $cvRiwayatHidupPath,
                 'surat_diterima' => $suratDiterimaPath,
                 'status_berkas' => $request->status_berkas ?? 'pending',
                 'tanggal_mulai' => $request->tanggal_mulai,
                 'tanggal_selesai' => $request->tanggal_selesai,
             ]);
 
             
            logActivity(null, null, 'create', 'Berkas', $berkas->id, null);

            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil menambahkan data',
                'data' => [
                    'user' => $user,
                    'master_sekolah' => $masterSekolah,
                    'berkas' => $berkas
                ],
                'token' => $token
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

    public function cekBerkas($nomor_registrasi){
        try {
            if (!preg_match('/^BERKAS-\d{8}-[A-Z0-9]+$/', $nomor_registrasi)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Format nomor registrasi tidak valid'
                ], 400);
            }

            $berkas = Berkas::where('nomor_registrasi', $nomor_registrasi)
                            ->join('users', 'berkas.user_id', '=', 'users.id')
                            ->select(
                                'users.email', 
                                'users.password',
                                'berkas.id',
                                'berkas.nomor_registrasi',
                                'berkas.status_berkas',
                                'berkas.created_at',
                                'berkas.updated_at',
                            )->first();

            if (!$berkas) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ], 404);
            } 
            return response()->json([
                'status' => 'success',
                'message' => 'Data ditemukan',
                'data' => $berkas
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mendapatkan data',
                'error' => $th->getMessage()
            ], 500);
        }
       
    }

    // admin 
    public function index()
    {
        try {
            $berkas = Berkas::get();
            return response()->json([
                'status' => 'success',
                'message' => 'Data berkas berhasil diambil',
                'data' => $berkas
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        } 
    }

    public function show(string $id)
    {
        try {
            $berkas = Berkas::find($id);
            if (!$berkas) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Berkas tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data berkas berhasil diambil',
                'data' => $berkas
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        } 
    }

    public function update(Request $request, string $id)
    {
        $berkas = Berkas::find($id);
        if (!$berkas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Berkas tidak ditemukan'
            ], 404);  
        }
        $oldData = $berkas->toArray(); 
        $berkasValidator = Validator::make($request->all(), [ 
            'status' => 'required|in:terima,pending,tolak',  
            
        ]); 
        if($berkasValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $berkasValidator->errors()
            ], 422);
        } 

        DB::beginTransaction();
        try {
            $berkas->update([ 
                'status' => $request->status
            ]);
            $newData = $berkas->toArray();
            $user = Auth::guard('sanctum')->user();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;

            logActivity($berkas->id, $nama, 'update', 'User', $berkas->id, [
                'old' => $oldData,
                'new' => $newData,
            ]);
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengupdate data user', 
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

}
