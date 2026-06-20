<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesertaKelas extends Model
{
    // Kunci nama tabel sesuai migration kamu
    protected $table = 'peserta_kelas';

    // Kolom yang diizinkan untuk diisi
    protected $fillable = [
        'kelas_id', 
        'siswa_id'
    ];
}