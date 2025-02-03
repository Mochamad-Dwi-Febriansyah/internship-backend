<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

use function App\Providers\logActivity;

class AuthController extends Controller
{
   public function login(Request $request)
   {
    try {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'error' => $validator->errors()
            ],422);
        }


        // Cek kredensial user
        if (! $token = JWTAuth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login gagal',
                'error' => 'Email atau password salah'
            ], 401);
        }

        // Ambil user yang sedang login
        $user = Auth::user();

        // Cek apakah user masih "inactive"
        if ($user->status === 'inactive') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun belum aktif. Silakan hubungi admin.'
            ], 403);
        } 
        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil', 
            'user' => [
                'id' => $user->id,
                'nama_depan' => $user->nama_depan,
                'nama_belakang' => $user->nama_belakang,
                'email' => $user->email
            ],
            'token' => $token,
        ], 200);
    } catch (JWTException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal membuat token',
            'error' => $e->getMessage()
        ], 500);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat login',
            'error' => $th->getMessage()
        ], 500);
    }
       
   }

   public function profile(Request $request, $id)
   {
        DB::beginTransaction();
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            // dd($user);
            $oldData = $user->toArray();
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
            $user->update([
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
            ]);
            $newData = $user->toArray();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;

            logActivity($user->id, $nama, 'update', 'User', $user->id, [
                'old' => $oldData,
                'new' => $newData,
            ]);
            DB::commit(); 
            if ($user) {
                return response()->json(['status' => 'success','message' => 'Data user berhasil diperbarui']);
            } else {
                return response()->json(['status' => 'error','message' => 'User tidak ditemukan'], 404);
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui data',
                'error' => $th->getMessage()
            ], 500);
        }
        
   } 
   
   public function getUser()
   {
       try {
           // Ambil user dari token
           $user = JWTAuth::parseToken()->authenticate();
   
           if (! $user) {
               return response()->json([
                   'status' => 'error',
                   'message' => 'User tidak ditemukan'
               ], 404);
           }
   
           return response()->json([
               'status' => 'success',
               'message' => 'User berhasil ditemukan',
               'user' => $user
           ], 200);
       } catch (TokenExpiredException $e) {
           return response()->json([
               'status' => 'error',
               'message' => 'Token telah kedaluwarsa'
           ], 401);
       } catch (TokenInvalidException $e) {
           return response()->json([
               'status' => 'error',
               'message' => 'Token tidak valid'
           ], 401);
       } catch (JWTException $e) {
           return response()->json([
               'status' => 'error',
               'message' => 'Token tidak ditemukan'
           ], 401);
       } catch (\Throwable $th) {
           return response()->json([
               'status' => 'error',
               'message' => 'Terjadi kesalahan saat mengambil data user',
               'error' => $th->getMessage()
           ], 500);
       }
   }
   

   public function logout(Request $request){    
    try {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ]);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat logout',
            'error' => $th->getMessage()
        ], 500);
    }
       
   }
}
