<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
 
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

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'status' => 'inactive'])) {
            $token = $request->user()->createToken('myAppToken')->plainTextToken;
            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil',
                'token' => $token
            ], 200); 
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Login gagal',
            'error' => 'Email atau password salah'
        ], 401);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat memperbarui data',
            'error' => $th->getMessage()
        ], 500);
    }
       
   }

   public function profile(Request $request, $uuid_user)
   {
        DB::beginTransaction();
        try {
            $user = User::where('id', $uuid_user)->update([
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

   public function logout(Request $request){    
    try {
        $user = Auth::guard('sanctum')->user();
        $user->currentAccessToken()->delete(); // merah tapi bisa 

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ]);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat memperbarui data',
            'error' => $th->getMessage()
        ], 500);
    }
       
   }
}
