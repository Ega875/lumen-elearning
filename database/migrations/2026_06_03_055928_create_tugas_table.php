<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTugasTable extends Migration
{
    public function up()
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->id(); // Primary Key otomatis bertipe BigInteger
            $table->integer('kelas_id'); // Untuk relasi ke kelas (jika ada tabel kelas)
            $table->unsignedBigInteger('user_id'); // Foreign key ke tabel users (Guru yang membuat)
            $table->string('judul_tugas', 150);
            $table->text('deskripsi')->nullable();
            $table->string('lampiran_file')->nullable(); // Menyimpan nama file materi/soal
            $table->dateTime('deadline');
            $table->timestamps(); // Otomatis membuat kolom created_at dan updated_at sesuai ERD

            // Definisi Foreign Key ke tabel users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tugas');
    }
}