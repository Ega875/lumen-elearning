<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request)
    {
        // 1. Ambil input dari form
        $nomorInduk = $request->input('nomor_induk');
        $password = $request->input('password');

        // 2. Hitung jumlah digit untuk menentukan Role
        $panjangDigit = strlen($nomorInduk);

        if ($panjangDigit === 5) {
            $role = 'siswa';
        } elseif ($panjangDigit === 8) {
            $role = 'guru';
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'ID tidak valid! Siswa harus 5 digit, Guru harus 8 digit.'
            ], 400);
        }

        // 3. Cari user di database berdasarkan nomor_induk DAN role
        $user = User::where('nomor_induk', $nomorInduk)
                    ->where('role', $role)
                    ->first();

        // 4. Validasi User & Password
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor Induk atau Password salah!'
            ], 401);
        }

        // 5. Jika berhasil
        return response()->json([
            'status' => 'success',
            'message' => 'Selamat datang, ' . $user->nama_lengkap,
            'role_anda' => $user->role,
            'data' => $user
        ]);
    }
}