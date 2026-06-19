<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tugas;
use Illuminate\Support\Str;

class TugasController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input (Format file dibatasi Word/PDF, Opsional, Max 10MB)
        // Perhatikan: 'user_id' sudah kita hapus dari validasi sini
        $this->validate($request, [
            'kelas_id'      => 'required|integer',
            'judul_tugas'   => 'required|string|max:150',
            'deskripsi'     => 'nullable|string',
            'deadline'      => 'required|date',
            'lampiran_file' => 'nullable|file|mimes:doc,docx,pdf|max:10240' 
        ]);

        try {
            $tugas = new Tugas();
            $tugas->kelas_id      = $request->input('kelas_id');
            
            // 2. Baca ID Guru otomatis dari Token JWT
            $tugas->user_id       = $request->auth->id; 
            
            $tugas->judul_tugas   = $request->input('judul_tugas');
            $tugas->deskripsi     = $request->input('deskripsi');
            $tugas->deadline      = $request->input('deadline');

            // 3. Proses Upload File Jika Ada
            if ($request->hasFile('lampiran_file')) {
                $file = $request->file('lampiran_file');
                
                // Menyusun nama file unik berdasarkan tanggal dan judul tugas
                $slugJudul = Str::slug($request->input('judul_tugas'), '_');
                $namaFile = date('YmdHis') . '_' . $slugJudul . '.' . $file->getClientOriginalExtension();
                
                // Folder tujuan di public/uploads/tugas
                $tujuanSimpan = base_path('public/uploads/tugas');
                
                // Pindahkan file fisik ke folder proyek
                $file->move($tujuanSimpan, $namaFile);
                
                // Simpan nama file ke database
                $tugas->lampiran_file = $namaFile;
            } else {
                $tugas->lampiran_file = null;
            }

            // Simpan ke database MySQL via MAMP
            $tugas->save();

            // 4. Response Sukses JSON
            return response()->json([
                'success' => true,
                'message' => 'Tugas baru berhasil diterbitkan otomatis oleh sistem!',
                'data'    => $tugas
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat tugas: ' . $e->getMessage()
            ], 500);
        }
    }

    // 1. Mengambil SEMUA daftar tugas
    public function index()
    {
        try {
            // Mengambil semua data tugas dan diurutkan dari yang paling baru
            $tugas = Tugas::orderBy('created_at', 'DESC')->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar semua tugas berhasil diambil',
                'data'    => $tugas
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tugas: ' . $e->getMessage()
            ], 500);
        }
    }

    // 2. Mengambil DETAIL satu tugas berdasarkan ID Tugas
    public function show($id)
    {
        try {
            // Mencari tugas berdasarkan primary key (id_tugas)
            $tugas = Tugas::find($id);

            // Jika tugas tidak ditemukan
            if (!$tugas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas tidak ditemukan!'
                ], 404);
            }

            // Jika ditemukan, kembalikan datanya beserta link unduh file (jika ada)
            $fileUrl = $tugas->lampiran_file ? url('uploads/tugas/' . $tugas->lampiran_file) : null;

            return response()->json([
                'success' => true,
                'message' => 'Detail tugas berhasil ditemukan',
                'data'    => $tugas,
                'download_url' => $fileUrl // Link langsung untuk download file PDF/Word-nya nanti di Frontend
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail tugas: ' . $e->getMessage()
            ], 500);
        }
    }

    // 3. EDIT / UPDATE TUGAS
    // Menggunakan Request $request dan parameter $id
    public function update(Request $request, $id)
    {
        // Cari tugasnya dulu
        $tugas = Tugas::find($id);

        if (!$tugas) {
            return response()->json(['success' => false, 'message' => 'Tugas tidak ditemukan!'], 404);
        }

        // KEAMANAN TAMBAHAN: Pastikan guru hanya bisa mengedit tugas buatannya sendiri!
        if ($tugas->user_id != $request->auth->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak! Anda bukan pembuat tugas ini.'], 403);
        }

        // Validasi Input
        $this->validate($request, [
            'kelas_id'      => 'required|integer',
            'judul_tugas'   => 'required|string|max:150',
            'deskripsi'     => 'nullable|string',
            'deadline'      => 'required|date',
            'lampiran_file' => 'nullable|file|mimes:doc,docx,pdf|max:10240' 
        ]);

        try {
            $tugas->kelas_id    = $request->input('kelas_id');
            $tugas->judul_tugas = $request->input('judul_tugas');
            $tugas->deskripsi   = $request->input('deskripsi');
            $tugas->deadline    = $request->input('deadline');

            // Cek apakah Guru mengunggah file baru untuk menggantikan yang lama
            if ($request->hasFile('lampiran_file')) {
                // Hapus file fisik lama jika ada
                if ($tugas->lampiran_file) {
                    $fileLama = base_path('public/uploads/tugas/' . $tugas->lampiran_file);
                    if (file_exists($fileLama)) {
                        unlink($fileLama); // Perintah PHP untuk menghapus file
                    }
                }

                // Proses simpan file baru
                $file = $request->file('lampiran_file');
                $slugJudul = Str::slug($request->input('judul_tugas'), '_');
                $namaFile = date('YmdHis') . '_' . $slugJudul . '.' . $file->getClientOriginalExtension();
                $file->move(base_path('public/uploads/tugas'), $namaFile);
                
                $tugas->lampiran_file = $namaFile;
            }

            $tugas->save();

            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil diperbarui!',
                'data'    => $tugas
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal update tugas: ' . $e->getMessage()], 500);
        }
    }

    // 4. HAPUS TUGAS
    public function destroy(Request $request, $id)
    {
        $tugas = Tugas::find($id);

        if (!$tugas) {
            return response()->json(['success' => false, 'message' => 'Tugas tidak ditemukan!'], 404);
        }

        // KEAMANAN TAMBAHAN: Pastikan guru hanya bisa menghapus tugas buatannya sendiri
        if ($tugas->user_id != $request->auth->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak! Anda bukan pembuat tugas ini.'], 403);
        }

        try {
            // Hapus file fisiknya dari folder Mac kamu (jika ada file-nya)
            if ($tugas->lampiran_file) {
                $fileLama = base_path('public/uploads/tugas/' . $tugas->lampiran_file);
                if (file_exists($fileLama)) {
                    unlink($fileLama);
                }
            }

            // Hapus data dari database
            $tugas->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tugas dan file lampirannya berhasil dihapus permanen!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus tugas: ' . $e->getMessage()], 500);
        }
    }
}