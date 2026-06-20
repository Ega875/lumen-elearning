<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengumpulanTugas extends Model
{
    // Kunci ke nama tabel di migration kamu
    protected $table = 'pengumpulan_tugas';

    // Kolom yang diizinkan untuk diisi
    protected $fillable = [
        'tugas_id',
        'user_id',
        'file_jawaban',
        'nilai',
        'submitted'
    ];
}