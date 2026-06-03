<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengumpulanTugasTable extends Migration
{
    public function up()
    {
        Schema::create('pengumpulan_tugas', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->unsignedBigInteger('tugas_id'); // Foreign key dari tabel tugas
            $table->unsignedBigInteger('user_id'); // Foreign key dari tabel users (Siswa yang mengumpulkan)
            $table->string('file_jawaban');
            $table->integer('nilai')->nullable(); // Nullable karena diisi nanti oleh guru saat penilaian
            $table->dateTime('submitted')->useCurrent(); // Mencatat waktu mengumpulkan otomatis
            $table->timestamps();

            // Setup Foreign Key Constraints
            $table->foreign('tugas_id')->references('id')->on('tugas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengumpulan_tugas');
    }
}