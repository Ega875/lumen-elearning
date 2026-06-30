<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diskusi; // Panggil model yang baru saja dibuat
use App\Models\Kelas;
use App\Models\PesertaKelas;

class DiskusiController extends Controller
{
    // 1. FUNGSI MENGIRIM PESAN
    public function store(Request $request, $kelasId)
    {
        // Validasi input: Sesuaikan dengan nama key 'isi_pesan'
        $this->validate($request, [
            'isi_pesan' => 'required|string'
        ]);

        // Cek apakah kelasnya ada
        $kelas = Kelas::find($kelasId);
        if (!$kelas) {
            return response()->json(['success' => false, 'message' => 'Kelas tidak ditemukan!'], 404);
        }

        // KEAMANAN: Pastikan pengirim adalah anggota kelas (Guru pembuat kelas ATAU Siswa di kelas tersebut)
        $userId = $request->auth->id;
        $isGuru = ($kelas->guru_id == $userId);
        $isSiswa = PesertaKelas::where('kelas_id', $kelasId)->where('siswa_id', $userId)->exists();

        if (!$isGuru && !$isSiswa) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak! Anda bukan anggota kelas ini.'], 403);
        }

        try {
            // Simpan pesan ke database
            $diskusi = new Diskusi();
            $diskusi->kelas_id  = $kelasId;
            $diskusi->user_id   = $userId;
            $diskusi->isi_pesan = $request->input('isi_pesan');
            $diskusi->save();

            return response()->json([
                'success' => true,
                'message' => 'Pesan terkirim!',
                'data'    => $diskusi
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim pesan: ' . $e->getMessage()], 500);
        }
    }

    // 2. FUNGSI MELIHAT RIWAYAT OBROLAN
    public function index(Request $request, $kelasId)
    {
        // Pastikan kelasnya ada
        $kelas = Kelas::find($kelasId);
        if (!$kelas) {
            return response()->json(['success' => false, 'message' => 'Kelas tidak ditemukan!'], 404);
        }

        // KEAMANAN: Jangan izinkan user asing mengintip isi diskusi
        $userId = $request->auth->id;
        $isGuru = ($kelas->guru_id == $userId);
        $isSiswa = PesertaKelas::where('kelas_id', $kelasId)->where('siswa_id', $userId)->exists();

        if (!$isGuru && !$isSiswa) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak! Anda bukan anggota kelas ini.'], 403);
        }

        try {
            // Ambil semua pesan dan urutkan dari yang terlama ke terbaru (asc)
            $obrolan = Diskusi::where('kelas_id', $kelasId)
                            ->orderBy('created_at', 'asc')
                            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Riwayat diskusi berhasil diambil!',
                'data'    => $obrolan
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memuat diskusi: ' . $e->getMessage()], 500);
        }
    }
}