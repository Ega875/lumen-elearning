<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Pastikan nama tabelnya 'users' (bentuk jamak)
        DB::table('users')->insert([
            [
                'nama_lengkap' => 'Pak Ega (Guru)',
                'email' => 'guru@elearning.com',
                'password' => Hash::make('password123'), // Meng-hash password agar aman
                'role' => 'guru',
                'nomor_induk' => 'GURU001',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama_lengkap' => 'Siswa Kelompok 1',
                'email' => 'siswa@elearning.com',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'nomor_induk' => 'SISWA001',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ]);
    }
}