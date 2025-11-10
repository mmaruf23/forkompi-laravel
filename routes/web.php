<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::fallback(function () {
    // Memastikan file index.html ada di public/
    $path = public_path('index.html');

    if (File::exists($path)) {
        return File::get($path);
    }

    // Jika index.html tidak ada (misalnya untuk error 404 asli)
    abort(404);
});