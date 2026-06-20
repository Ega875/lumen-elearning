<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi input dari Frontend (Laravel)
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. queryUserByEmail() - Mencari user di database berdasarkan email
        $user = User::where('email', $request->input('email'))->first();

        // 3. Jika user tidak ditemukan
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak terdaftar!'
            ], 401);
        }

        // 4. verifyPassword() - Mencocokkan password input dengan password ter-hash di DB
        if (Hash::check($request->input('password'), $user->password)) {
            
            // 5. generateToken() - Menyusun data isi token (Payload)
            $payload = [
                'iss' => "lumen-elearning", // Nama aplikasi
                'sub' => $user->id,         // ID unik user
                'role' => $user->role,       // Role user (guru / siswa)
                'iat' => time(),             // Waktu token dibuat
                'exp' => time() + 60*60*2    // Token otomatis hangus dalam 2 jam
            ];
            
            // Generate token string menggunakan key rahasia dari .env
            $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

            // 6. JSON Response (Token, Role, Success) sesuai Sequence Diagram kamu!
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil!',
                'token' => $token,
                'role' => $user->role,
                'user' => [
                    'nama_lengkap' => $user->nama_lengkap, // Menyesuaikan kolom barumu kemarin
                    'nomor_induk'  => $user->nomor_induk
                ]
            ], 200);
        }

        // Jika password salah
        return response()->json([
            'success' => false,
            'message' => 'Password yang kamu masukkan salah!'
        ], 401);
    }

    public function register(Request $request)
    {
        // 1. Validasi Input: Tambahkan wajib isi nomor_induk
        $this->validate($request, [
            'nama_lengkap' => 'required|string|max:100', 
            'nomor_induk'  => 'required|string|unique:users', // Pastikan NIS/NIM tidak boleh sama
            'email'        => 'required|email|unique:users',
            'password'     => 'required|string|min:6',
        ]);

        try {
            // 2. Simpan ke Database
            $user = new \App\Models\User();
            
            $user->nama_lengkap = $request->input('nama_lengkap'); 
            // TAMBAHKAN BARIS INI:
            $user->nomor_induk  = $request->input('nomor_induk'); 
            
            $user->email        = $request->input('email');
            $user->password     = Hash::make($request->input('password')); 
            $user->role         = 'siswa'; 
            
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Registrasi Siswa berhasil! Silakan lakukan login.',
                'data'    => [
                    'id'           => $user->id,
                    'nomor_induk'  => $user->nomor_induk, // Tampilkan juga di response
                    'nama_lengkap' => $user->nama_lengkap,
                    'email'        => $user->email,
                    'role'         => $user->role
                ]
            ], 201); 

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registrasi gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}