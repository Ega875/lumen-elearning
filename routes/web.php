<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Route tanpa prefix 'api'
$router->get('/users', 'UserController@index');

/*
|--------------------------------------------------------------------------
| Grup Rute API
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'api'], function () use ($router) {
    
    // 🟢 JALUR BEBAS: Bisa diakses tanpa token
    $router->post('/login', 'AuthController@login');
    $router->post('/register', 'AuthController@register'); // --- TAMBAHKAN BARIS INI ---
    
    $router->get('/test-hello', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'Hello World! API Lumen berhasil terhubung.',
            'timestamp' => date('Y-m-d H:i:s')
        ], 200);
    });

    // 🔴 JALUR TERKUNCI: Wajib membawa Token JWT (Dilindungi Middleware)
    $router->group(['middleware' => 'jwt.auth'], function () use ($router) {
        
        // Manajemen Tugas
        $router->post('/tugas', 'TugasController@store');      // Buat tugas
        $router->get('/tugas', 'TugasController@index');       // Tampilkan semua tugas
        $router->get('/tugas/{id}', 'TugasController@show');   // Tampilkan detail satu tugas
        $router->post('/tugas/{id}', 'TugasController@update');  // Edit tugas (Tetap pakai POST karena ada upload file)
        $router->delete('/tugas/{id}', 'TugasController@destroy'); // Hapus tugas

        // Manajemen Pengumpulan Tugas
        $router->post('/tugas/{tugasId}/kumpul', 'PengumpulanController@store');// Siswa kumpulkan tugas (upload jawaban)
        $router->get('/tugas/{tugasId}/jawaban', 'PengumpulanController@listJawaban'); // Guru lihat jawaban
        $router->post('/jawaban/{id}/nilai', 'PengumpulanController@beriNilai');  // Guru beri nilai

        // Manajemen Kelas
        $router->post('/kelas', 'KelasController@store');      // Guru bikin kelas
        $router->post('/kelas/join', 'KelasController@join');  // Siswa gabung kelas
        $router->get('/kelas', 'KelasController@index');       // Guru lihat daftar kelas
        $router->delete('/kelas/{id}/leave', 'KelasController@leave'); // Siswa keluar kelas
        
        // --- FORUM DISKUSI ---
        $router->post('/kelas/{kelasId}/diskusi', 'DiskusiController@store'); // Kirim pesan
        $router->get('/kelas/{kelasId}/diskusi', 'DiskusiController@index');  // Lihat semua pesan
    });

});