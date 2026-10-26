<?php

use App\Http\Controllers\Admin\AkunController;
use App\Http\Controllers\Admin\AturanPrestasiController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\KegiatanPembinaanController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\NilaiController;
use App\Http\Controllers\Admin\PenerimaController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\PeringatanController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use App\Http\Controllers\Mahasiswa\NilaiController as MahasiswaNilaiController;
use App\Http\Controllers\Mahasiswa\PembinaanController;
use App\Http\Controllers\NotifikasiController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/login', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store'])->name('login.store');
Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/{jenis}/excel', [LaporanController::class, 'excel'])
        ->whereIn('jenis', ['nilai', 'kehadiran'])
        ->name('laporan.excel');
    Route::get('/laporan/pdf-semester', [LaporanController::class, 'pdfSemester'])
        ->name('laporan.pdf-semester');
    Route::get('/penerima/import', [PenerimaController::class, 'importForm'])->name('penerima.import-form');
    Route::post('/penerima/import', [PenerimaController::class, 'import'])->name('penerima.import');
    Route::get('/penerima/{penerima}/nilai', [NilaiController::class, 'index'])->name('penerima.nilai.index');
    Route::get('/penerima/{penerima}/nilai/create', [NilaiController::class, 'create'])->name('penerima.nilai.create');
    Route::post('/penerima/{penerima}/nilai', [NilaiController::class, 'store'])->name('penerima.nilai.store');
    Route::get('/penerima/{penerima}/nilai/{semester}/edit', [NilaiController::class, 'edit'])->name('penerima.nilai.edit');
    Route::put('/penerima/{penerima}/nilai/{semester}', [NilaiController::class, 'update'])->name('penerima.nilai.update');
    Route::get('/nilai/{semester}/khs', [NilaiController::class, 'downloadForAdmin'])->name('nilai.khs');
    Route::resource('aturan-prestasi', AturanPrestasiController::class)->except(['show']);
    Route::get('/peringatan', [PeringatanController::class, 'index'])->name('peringatan.index');
    Route::post('/peringatan/{mahasiswa}/cabut', [PeringatanController::class, 'cabut'])->name('peringatan.cabut');
    Route::get('/pengaturan', [PengaturanController::class, 'edit'])->name('pengaturan.edit');
    Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
    Route::get('/akun', [AkunController::class, 'index'])->name('akun.index');
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
    Route::put('/akun/{akun}/password', [AkunController::class, 'resetPassword'])->name('akun.password');
    Route::put('/akun/{akun}/status', [AkunController::class, 'toggleAktif'])->name('akun.status');
    Route::get('/rekap/mahasiswa/{mahasiswa}/kehadiran', [KegiatanPembinaanController::class, 'rekapMahasiswa'])->name('kehadiran.mahasiswa');
    Route::get('/absensi/{absensi}/foto', [KegiatanPembinaanController::class, 'foto'])->name('absensi.foto');
    Route::post('/kegiatan/{kegiatan}/absensi/{mahasiswa}', [KegiatanPembinaanController::class, 'koreksiAbsensi'])->name('kegiatan.absensi.koreksi');
    Route::resource('kegiatan', KegiatanPembinaanController::class);
    Route::resource('penerima', PenerimaController::class);
});

Route::middleware(['auth', 'role:mahasiswa'])
    ->get('/mahasiswa/nilai', [MahasiswaNilaiController::class, 'index'])
    ->name('mahasiswa.nilai.index');

Route::middleware(['auth', 'role:mahasiswa'])
    ->get('/mahasiswa/dashboard', [MahasiswaDashboardController::class, 'index'])
    ->name('mahasiswa.dashboard');

Route::middleware(['auth', 'role:mahasiswa'])->group(function () {
    Route::get('/mahasiswa/pembinaan', [PembinaanController::class, 'index'])->name('mahasiswa.pembinaan.index');
    Route::post('/mahasiswa/pembinaan/{kegiatan}/absen', [PembinaanController::class, 'absen'])->name('mahasiswa.pembinaan.absen');
    Route::get('/mahasiswa/absensi/{absensi}/foto', [PembinaanController::class, 'foto'])->name('mahasiswa.absensi.foto');
});

Route::middleware(['auth', 'role:mahasiswa'])
    ->get('/mahasiswa/khs/{semester}', [NilaiController::class, 'downloadForMahasiswa'])
    ->name('mahasiswa.khs.download');
