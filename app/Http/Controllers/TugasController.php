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
        $this->validate($request, [
            'kelas_id'      => 'required|integer',
            'user_id'       => 'required|integer', // Diisi manual sementara sebelum middleware aktif
            'judul_tugas'   => 'required|string|max:150',
            'deskripsi'     => 'nullable|string',
            'deadline'      => 'required|date',
            'lampiran_file' => 'nullable|file|mimes:doc,docx,pdf|max:10240' 
        ]);

        try {
            $tugas = new Tugas();
            $tugas->kelas_id      = $request->input('kelas_id');
            $tugas->user_id       = $request->input('user_id');
            $tugas->judul_tugas   = $request->input('judul_tugas');
            $tugas->deskripsi     = $request->input('deskripsi');
            $tugas->deadline      = $request->input('deadline');

            // 2. Proses Upload File Jika Ada
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

            // 3. Response Sukses JSON
            return response()->json([
                'success' => true,
                'message' => 'Tugas baru berhasil diterbitkan!',
                'data'    => $tugas
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat tugas: ' . $e->getMessage()
            ], 500);
        }
    }
}