<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;

class PengumpulanController extends Controller
{
    // FUNGSI SISWA MENGUMPULKAN TUGAS
    public function store(Request $request, $tugasId)
    {
        // 1. Validasi Input (File jawaban wajib diisi, format PDF/Word, max 10MB)
        $this->validate($request, [
            'file_jawaban' => 'required|file|mimes:pdf,doc,docx|max:10240'
        ]);

        // 2. Pastikan tugas yang mau dikumpulkan benar-benar ada di database
        $tugas = Tugas::find($tugasId);
        if (!$tugas) {
            return response()->json(['success' => false, 'message' => 'Tugas tidak ditemukan!'], 404);
        }

        // 3. Cek apakah siswa ini sudah pernah mengumpulkan jawaban untuk tugas ini
        $pengumpulan = PengumpulanTugas::where('tugas_id', $tugasId)
                                    ->where('user_id', $request->auth->id)
                                    ->first();

        try {
            // Jika belum pernah kumpul, buat instansiasi baru
            if (!$pengumpulan) {
                $pengumpulan = new PengumpulanTugas();
                $pengumpulan->tugas_id = $tugasId;
                $pengumpulan->user_id  = $request->auth->id; // ID otomatis dari Token JWT Siswa
            }

            // 4. Proses Upload File Jawaban
            if ($request->hasFile('file_jawaban')) {
                // Jika sebelumnya sudah pernah kumpul (mau revisi file), hapus file lamanya
                if ($pengumpulan->file_jawaban) {
                    $fileLama = base_path('public/uploads/jawaban/' . $pengumpulan->file_jawaban);
                    if (file_exists($fileLama)) {
                        unlink($fileLama);
                    }
                }

                $file = $request->file('file_jawaban');
                
                // Bikin nama file unik: IDTugas_IDSiswa_Timestamp.ext (Misal: Tugas1_Siswa3_20260620.pdf)
                $namaFile = 'Tugas' . $tugasId . '_Siswa' . $request->auth->id . '_' . date('YmdHis') . '.' . $file->getClientOriginalExtension();
                
                // Simpan ke folder public/uploads/jawaban
                $file->move(base_path('public/uploads/jawaban'), $namaFile);
                
                $pengumpulan->file_jawaban = $namaFile;
            }

            // Memicu trigger timestamp manual untuk berjaga-jaga jika useCurrent() MAMP telat merespons
            $pengumpulan->submitted = date('Y-m-d H:i:s');
            
            // Simpan ke database
            $pengumpulan->save();

            return response()->json([
                'success' => true,
                'message' => 'Jawaban tugas berhasil dikumpulkan!',
                'data'    => $pengumpulan
            ], 201); // 201 Created

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengumpulkan tugas: ' . $e->getMessage()
            ], 500);
        }
    }

    // FUNGSI GURU MELIHAT DAFTAR JAWABAN SISWA
    // Parameter $tugasId untuk melihat tugas spesifik mana yang mau dicek
    // FUNGSI GURU MELIHAT DAFTAR JAWABAN SISWA (VERSI REVISI)
    // Parameter $tugasId untuk melihat tugas spesifik mana yang mau dicek
    public function listJawaban($tugasId)
    {
        try {
            // 🔴 PERUBAHAN DI SINI: Melakukan JOIN dengan tabel 'users' untuk mengambil 'nama_lengkap' siswa
            $pengumpulan = PengumpulanTugas::join('users', 'pengumpulan_tugas.user_id', '=', 'users.id')
                            ->where('pengumpulan_tugas.tugas_id', $tugasId)
                            ->select('pengumpulan_tugas.*', 'users.nama_lengkap') // Mengambil semua kolom tugas + nama lengkap siswa
                            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar jawaban siswa berhasil diambil!',
                'data'    => $pengumpulan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar jawaban: ' . $e->getMessage()
            ], 500);
        }
    }

    // FUNGSI GURU MEMBERIKAN NILAI
    // Parameter $id adalah primary key dari tabel pengumpulan_tugas
    public function beriNilai(Request $request, $id)
    {
        // Validasi input: Nilai harus berupa angka dari 0 sampai 100
        $this->validate($request, [
            'nilai' => 'required|integer|min:0|max:100'
        ]);

        // Cari data jawaban siswa tersebut di database
        $pengumpulan = PengumpulanTugas::find($id);

        if (!$pengumpulan) {
            return response()->json([
                'success' => false, 
                'message' => 'Data jawaban siswa tidak ditemukan!'
            ], 404);
        }

        try {
            // Update kolom 'nilai' dengan inputan dari Guru
            $pengumpulan->nilai = $request->input('nilai');
            $pengumpulan->save();

            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil diberikan!',
                'data'    => $pengumpulan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memberikan nilai: ' . $e->getMessage()
            ], 500);
        }
    }
}