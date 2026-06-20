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

        // Manajemen Kelas
        $router->post('/kelas', 'KelasController@store');      // Guru bikin kelas
        $router->post('/kelas/join', 'KelasController@join');  // Siswa gabung kelas
        $router->delete('/kelas/{id}/leave', 'KelasController@leave'); // Siswa keluar kelas
    });

});