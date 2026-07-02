<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    // Beri tahu Lumen nama tabel pastinya
    protected $table = 'materi';

    // Kolom yang boleh diisi
    protected $fillable = [
        'kelas_id',
        'judul_materi',
        'isi_materi',
        'lampiran_file',
    ];

    // Relasi: Setiap materi dimiliki oleh satu kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
}