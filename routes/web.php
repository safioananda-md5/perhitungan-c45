<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\MainController;
use App\Http\Controllers\ExcelController;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [MainController::class, 'index'])->name('main');

// Route untuk memproses unggah file dan menyimpan ke cache
Route::post('/excel-upload', [ExcelController::class, 'importToCache'])->name('excel.import.cache');

// Route untuk menampilkan data dari cache
Route::get('/excel-data', [ExcelController::class, 'showCachedData'])->name('excel.data.cached');

Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
    flash()->success('Cache berhasil dihapus!');
    return redirect()->back();
})->name('clear.cache');
