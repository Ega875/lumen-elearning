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
    // FUNGSI MELIHAT RIWAYAT OBROLAN (VERSI REVISI JOIN USERS)
    public function index(Request $request, $kelasId)
    {
        $kelas = Kelas::find($kelasId);
        if (!$kelas) {
            return response()->json(['success' => false, 'message' => 'Kelas tidak ditemukan!'], 404);
        }

        $userId = $request->auth->id;
        $isGuru = ($kelas->guru_id == $userId);
        $isSiswa = PesertaKelas::where('kelas_id', $kelasId)->where('siswa_id', $userId)->exists();

        if (!$isGuru && !$isSiswa) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak! Anda bukan anggota kelas ini.'], 403);
        }

        try {
            // 🔴 PERUBAHAN: Join dengan tabel users untuk mengambil nama_lengkap pengirim chat
            $obrolan = Diskusi::join('users', 'diskusi.user_id', '=', 'users.id')
                            ->where('diskusi.kelas_id', $kelasId)
                            ->orderBy('diskusi.created_at', 'asc')
                            ->select('diskusi.*', 'users.nama_lengkap') // Mengambil data chat + nama lengkap
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