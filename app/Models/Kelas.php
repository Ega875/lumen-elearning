<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    // Mengunci nama tabel
    protected $table = 'kelas';

    // Kolom yang diizinkan untuk diisi
    protected $fillable = [
        'guru_id',     // Sesuai dengan migration kamu
        'nama_kelas', 
        'kode_kelas',
        'deskripsi'
    ];
}