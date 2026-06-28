<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Di sini kita menentukan guard default untuk aplikasi EduLearn.
    | Kita atur default-nya langsung menggunakan guard 'api'.
    |
    */

    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Di sini mendefinisikan guard api agar driver-nya menggunakan 'jwt'.
    | Ini yang krusial untuk memperbaiki eror driver not defined kemarin.
    |
    */

    'guards' => [
        'api' => [
            'driver' => 'jwt', // <--- Memakai JWT Auth sebagai driver token
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Di sini kita menentukan bagaimana user/siswa dicari di database.
    | Secara default menggunakan Eloquent Model 'User'.
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class, // <--- Sesuaikan jika nama/letak model User kalian berbeda
        ],
    ],
];