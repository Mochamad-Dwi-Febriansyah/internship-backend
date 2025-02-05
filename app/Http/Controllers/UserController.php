<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

use function App\Providers\logActivity;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = Cache::remember('users_list', 600, function () {
                return User::where('role', 'user')->get();
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $users
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
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $userValidator->errors()
            ], 422);
        } 

        DB::beginTransaction();
        try {
            $userCreate = User::create([
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
            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'create', 'User', $userCreate->id, null);

            DB::commit();
 
            Cache::forget('users_list');
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil menambahkan data user',
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
            $cacheKey = "user_{$id}";
            $user = Cache::remember($cacheKey, 600, function () use ($id) {
                return User::find($id);
            });
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $user
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ], 404);  // Kode status 404, karena data tidak ditemukan
        }
        $oldData = $user->toArray();
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
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $userValidator->errors()
            ], 422);
        } 

        DB::beginTransaction();
        try {
            $user->update([
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
            $newData = $user->toArray();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;

            logActivity($user->id, $nama, 'update', 'User', $user->id, [
                'old' => $oldData,
                'new' => $newData,
            ]);
            DB::commit();

            Cache::forget('users_list');
            Cache::forget("user_{$id}");
    
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            $user->delete(); 

            $oldData = $user->toArray();
            $user->delete();
            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'delete', 'User', $user->id, [
                'old' => $oldData,
            ]);

            Cache::forget('users_list');
            Cache::forget("user_{$id}");
            
        return response()->json([
            'status' => 'success',
            'message' => 'Data user berhasil dihapus',  // Mengganti 'diambil' dengan 'dihapus'
        ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $th->getMessage()
            ], 500);
        } 
    }

    // mentor
    public function userMagangByMentor(){
        try {
            $mentorId = JWTAuth::parseToken()->authenticate()->id;
             $users = User::whereIn('id', function($query) use ($mentorId){
                $query->select('user_id')->from('berkas')->where('mentor_id', $mentorId);
             })->where('role', 'user')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    //kepegawaian 
     public function userMagang(){
        try { 
             $users = User::whereIn('id', function($query){
                $query->select('user_id')->from('berkas')->where('status_berkas', 'terima');
             })->where('role', 'user')->where('status', 'active')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
     public function userMentor(){
        try {
            $users = User::where('role', 'mentor')->where('status', 'active')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
