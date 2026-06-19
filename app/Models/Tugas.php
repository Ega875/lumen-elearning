<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    // Mengunci nama tabel sesuai hasil migrasi suksesmu
    protected $table = 'tugas';

    // Set Primary Key sesuai ERD
    protected $primaryKey = 'id';

    // Daftarkan semua kolom yang bisa diisi (Mass Assignable)
    protected $fillable = [
        'kelas_id',       // Menggunakan akhiran _id
        'user_id',        // ID Guru yang membuat tugas
        'judul_tugas', 
        'deskripsi', 
        'lampiran_file', 
        'deadline'
    ];
}