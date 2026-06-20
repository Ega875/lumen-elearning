<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diskusi extends Model
{
    // Kunci ke nama tabel di migration kamu
    protected $table = 'diskusi';

    // Kolom yang diizinkan untuk diisi
    protected $fillable = [
        'kelas_id',
        'user_id',
        'isi_pesan' // Sesuai dengan kolom di migration kamu
    ];
}