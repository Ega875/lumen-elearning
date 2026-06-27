<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use Illuminate\Support\Str;
use App\Models\PesertaKelas;

class KelasController extends Controller
{
    // FUNGSI MEMBUAT KELAS OLEH GURU
    public function store(Request $request)
    {
        // 1. Validasi Input dari Postman
        $this->validate($request, [
            'nama_kelas' => 'required|string|max:100',
            'deskripsi'  => 'nullable|string'
        ]);

        try {
            // 2. Generate Kode Kelas Acak (Contoh: A7X9WQ)
            $kodeAcak = strtoupper(Str::random(6));

            // 3. Simpan ke Database
            $kelas = new Kelas();
            $kelas->guru_id    = $request->auth->id; // ID otomatis dari Token JWT Guru
            $kelas->nama_kelas = $request->input('nama_kelas');
            $kelas->deskripsi  = $request->input('deskripsi');
            $kelas->kode_kelas = $kodeAcak;
            
            $kelas->save();

            // 4. Berikan Response Sukses
            return response()->json([
                'success' => true,
                'message' => 'Kelas baru berhasil dibuat!',
                'data'    => $kelas
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kelas: ' . $e->getMessage()
            ], 500);
        }
    }

    // FUNGSI BERGABUNG KE KELAS (OLEH SISWA)
    public function join(Request $request)
    {
        // 1. Validasi input: Siswa harus memasukkan kode kelas
        $this->validate($request, [
            'kode_kelas' => 'required|string'
        ]);

        $kode = $request->input('kode_kelas');

        // 2. Cari kelas di database berdasarkan kode unik tersebut
        $kelas = Kelas::where('kode_kelas', $kode)->first();

        if (!$kelas) {
            return response()->json([
                'success' => false, 
                'message' => 'Kode kelas tidak valid atau tidak ditemukan!'
            ], 404);
        }

        // 3. Pengecekan Duplikat: Cegah siswa join ke kelas yang sama dua kali
        $cekPeserta = PesertaKelas::where('kelas_id', $kelas->id)
                                ->where('siswa_id', $request->auth->id)
                                ->first();

        if ($cekPeserta) {
            return response()->json([
                'success' => false, 
                'message' => 'Anda sudah tergabung di kelas ini!'
            ], 400); // 400 Bad Request
        }

        try {
            // 4. Masukkan data ke tabel peserta_kelas
            $peserta = new PesertaKelas();
            $peserta->kelas_id = $kelas->id;
            $peserta->siswa_id = $request->auth->id; // ID Siswa otomatis terbaca dari Token JWT
            $peserta->save();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil bergabung ke kelas: ' . $kelas->nama_kelas,
                'data'    => $peserta
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal bergabung kelas: ' . $e->getMessage()
            ], 500);
        }
    }

    // FUNGSI KELUAR DARI KELAS (OLEH SISWA)
    // Parameter $id di sini adalah ID dari kelas (kelas_id)
    public function leave(Request $request, $id)
    {
        // 1. Cari data peserta di tabel peserta_kelas
        // Berdasarkan kelas_id dan siswa_id (dari Token JWT)
        $peserta = PesertaKelas::where('kelas_id', $id)
                            ->where('siswa_id', $request->auth->id)
                            ->first();

        // 2. Validasi: Jika data tidak ditemukan
        if (!$peserta) {
            return response()->json([
                'success' => false, 
                'message' => 'Anda tidak terdaftar di kelas ini!'
            ], 404);
        }

        try {
            // 3. Eksekusi Hapus Data
            $peserta->delete();

            return response()->json([
                'success' => true,
                'message' => 'Anda berhasil keluar dari kelas.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal keluar dari kelas: ' . $e->getMessage()
            ], 500);
        }
    }

    // FUNGSI UNTUK MENAMPILKAN DAFTAR KELAS GURU
public function index(Request $request)
{
    // Mengambil semua kelas milik guru yang sedang login (berdasarkan ID dari JWT)
    $kelas = Kelas::where('guru_id', $request->auth->id)->get();

    return response()->json([
        'success' => true,
        'data'    => $kelas
    ], 200);
}
}