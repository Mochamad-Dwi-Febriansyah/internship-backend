<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::where('role', 'user')->get();
            return response()->json([
                'message' => 'Data user berhasil diambil',
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
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
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
                'message' => 'Validasi gagal',
                'errors' => $userValidator->errors()
            ], 422);
        } 

        DB::beginTransaction();
        try {
            User::create([
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
            DB::commit();
    
            return response()->json([
                'message' => 'Berhasil menambahkan data user',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
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
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            return response()->json([
                'message' => 'Data user berhasil diambil',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
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
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan'
            ], 404);  // Kode status 404, karena data tidak ditemukan
        }
        // dd($user);
        $userValidator = Validator::make($request->all(), [
            'nisn_npm_nim' => 'max:20',
            'tanggal_lahir' => 'required|date',
            'nama_depan' => 'required',
            'nama_belakang' => 'nullable',
            'jenis_kelamin' => 'required|in:male,female',
            'nomor_hp' => 'required',Rule::unique('users')->ignore($user->id),'regex:/^\+?[\d\s\(\)-]+$/',
            'email' => 'required|email',Rule::unique('users')->ignore($user->id),
            'password' => 'nullable',
            'alamat' => 'required',
            'kabupaten_kota' => 'required',
            'provinsi' => 'required',
            'kode_pos' => 'required',
            'role' => 'required|in:admin,user',  
            
        ]); 
        if($userValidator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $userValidator->errors()
            ], 422);
        } 

        DB::beginTransaction();
        try {
            User::where('id', $id)->update([
                'nisn_npm_nim' => $request->nisn_npm_nim,
                'tanggal_lahir' => $request->tanggal_lahir,
                'nama_depan' => $request->nama_depan,
                'nama_belakang' => $request->nama_belakang,
                'jenis_kelamin' => $request->jenis_kelamin,
                'nomor_hp' => $request->nomor_hp,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) :  $user->password,
                'alamat' => $request->alamat,
                'kabupaten_kota' => $request->kabupaten_kota,
                'provinsi' => $request->provinsi,
                'kode_pos' => $request->kode_pos,
                'role' => $request->role
            ]);
            DB::commit();
    
            return response()->json([
                'message' => 'Berhasil mengupdate data user', 
            ], 204);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
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
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            $user->delete(); 
        return response()->json([
            'message' => 'Data user berhasil dihapus',  // Mengganti 'diambil' dengan 'dihapus'
        ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $th->getMessage()
            ], 500);
        } 
    }
}
