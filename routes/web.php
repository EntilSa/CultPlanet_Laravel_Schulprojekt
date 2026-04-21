<?php

use Illuminate\Support\Facades\Route;

// Startseite
Route::get('/', function () {
    return view('pages.startseite');
});

// Impressum
Route::get('/impressum', function () {
    return view('pages.impressum');
});

// Datenschutz
Route::get('/datenschutz', function () {
    return view('pages.datenschutz');
});
