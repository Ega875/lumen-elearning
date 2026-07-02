<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Kelas;
use Illuminate\Http\Request;

class MateriController extends Controller
{
    // Fungsi untuk Guru mengunggah materi
    public function store(Request $request, $kelasId)
    {
        // 1. Validasi input
        $this->validate($request, [
            'judul_materi' => 'required|string',
            'isi_materi' => 'required|string',
            'lampiran_file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip|max:10240' // Maksimal 10MB
        ]);

        // 2. Pastikan kelasnya ada
        $kelas = Kelas::find($kelasId);
        if (!$kelas) {
            return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
        }

        // 3. Proses upload file (jika ada)
        $namaFile = null;
        if ($request->hasFile('lampiran_file')) {
            $file = $request->file('lampiran_file');
            // Bikin nama file unik pakai time() biar nggak bentrok
            $namaFile = time() . '_' . $file->getClientOriginalName();
            // Pindahkan file ke folder public/uploads/materi
            $file->move('uploads/materi', $namaFile);
        }

        // 4. Simpan ke database
        $materi = Materi::create([
            'kelas_id' => $kelasId,
            'judul_materi' => $request->input('judul_materi'),
            'isi_materi' => $request->input('isi_materi'),
            'lampiran_file' => $namaFile,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Materi berhasil diunggah.',
            'data' => $materi
        ], 201);
    }

    // Fungsi untuk Siswa dan Guru melihat daftar materi di kelas
    public function index($kelasId)
    {
        $materi = Materi::where('kelas_id', $kelasId)->get();

        return response()->json([
            'status' => 'success',
            'data' => $materi
        ], 200);
    }
}