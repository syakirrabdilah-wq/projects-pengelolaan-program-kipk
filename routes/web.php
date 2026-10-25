<?php

use App\Http\Controllers\Admin\AturanPrestasiController;
use App\Http\Controllers\Admin\NilaiController;
use App\Http\Controllers\Admin\PenerimaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/penerima/import', [PenerimaController::class, 'importForm'])->name('penerima.import-form');
    Route::post('/penerima/import', [PenerimaController::class, 'import'])->name('penerima.import');
    Route::get('/penerima/{penerima}/nilai', [NilaiController::class, 'index'])->name('penerima.nilai.index');
    Route::get('/penerima/{penerima}/nilai/create', [NilaiController::class, 'create'])->name('penerima.nilai.create');
    Route::post('/penerima/{penerima}/nilai', [NilaiController::class, 'store'])->name('penerima.nilai.store');
    Route::get('/penerima/{penerima}/nilai/{semester}/edit', [NilaiController::class, 'edit'])->name('penerima.nilai.edit');
    Route::put('/penerima/{penerima}/nilai/{semester}', [NilaiController::class, 'update'])->name('penerima.nilai.update');
    Route::get('/nilai/{semester}/khs', [NilaiController::class, 'downloadForAdmin'])->name('nilai.khs');
    Route::resource('aturan-prestasi', AturanPrestasiController::class)->except(['show']);
    Route::resource('penerima', PenerimaController::class);
});

Route::middleware(['auth', 'role:mahasiswa'])
    ->get('/mahasiswa/khs/{semester}', [NilaiController::class, 'downloadForMahasiswa'])
    ->name('mahasiswa.khs.download');
