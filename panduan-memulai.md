# Panduan Memulai Proyek (Langkah demi Langkah)

> Mencakup tugas T-002 sampai T-007 dan T-101 sampai T-102 di `agents/task.md`.
> Perintah ditulis untuk Windows dengan Laragon; di XAMPP/Linux/macOS sama, hanya cara membuka terminal yang berbeda.

## Ringkasan urutan

1. Cek persyaratan (PHP, Composer, Git, ekstensi)
2. Buat proyek Laravel
3. Buat database dan atur `.env`
4. Git: commit pertama
5. Salin dokumen proyek ke repositori
6. Pasang paket tambahan
7. Buat migrasi
8. Model dan seeder
9. Login dengan `username` dan pembatas peran
10. Jalankan dan uji
11. Alur kerja harian

---

## Langkah 1 — Cek persyaratan

Buka terminal (di Laragon: tombol **Terminal**), lalu jalankan:

```bash
php -v
composer -V
git --version
mysql --version
```

Yang perlu dipastikan:

| Hal | Syarat |
|---|---|
| PHP | **8.3 atau lebih baru** bila memakai Laravel 13 (versi terbaru saat dokumen ini disusun). Laravel 12 cukup PHP 8.2. Cek ulang persyaratan di dokumentasi resmi Laravel (bagian Installation) sebelum memulai |
| Ekstensi PHP | `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip`, `gd` (dua terakhir dibutuhkan paket Excel dan PDF) |
| Composer | Terpasang dan bisa dipanggil dari terminal |
| MySQL/MariaDB | Berjalan (di Laragon: tombol **Start All**) |

Mengaktifkan ekstensi di Laragon: klik kanan ikon Laragon, **PHP > Extensions**, centang yang dibutuhkan.

Jika versi PHP di komputermu masih 8.2, ada dua pilihan: perbarui PHP (Laragon mendukung beberapa versi PHP berdampingan), atau pasang Laravel 12 pada langkah 2 dengan perintah alternatif.

Node.js **tidak wajib** karena Bootstrap dipasang lewat CDN pada awal pengerjaan.

## Langkah 2 — Buat proyek Laravel

Pindah ke folder kerja (di Laragon biasanya `C:\laragon\www`), lalu:

```bash
composer create-project laravel/laravel kipk
cd kipk
```

Alternatif bila PHP-mu 8.2:

```bash
composer create-project laravel/laravel:^12.0 kipk
```

Penting: **jangan memasang starter kit atau Breeze**. Starter kit Laravel terbaru berbasis React/Vue/Livewire dan menyertakan pendaftaran mandiri yang tidak kita butuhkan. Login kita buat manual di langkah 9 (hanya sekitar 60 baris kode).

Cek hasilnya:

```bash
php artisan --version
```

## Langkah 3 — Database dan `.env`

Buat database kosong (lewat HeidiSQL/phpMyAdmin, atau terminal):

```bash
mysql -u root -e "CREATE DATABASE kipk_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Buka `.env` dan ubah bagian berikut (sesuaikan username dan password MySQL-mu):

```dotenv
APP_NAME="KIP-K"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kipk_db
DB_USERNAME=root
DB_PASSWORD=

# Kata sandi awal untuk tiga akun admin (dipakai seeder, ganti setelah login pertama)
ADMIN_PASSWORD=isi-dengan-kata-sandi-kuat
```

Atur **zona waktu**, karena jendela absensi dan timestamp foto bergantung padanya. Buka `config/app.php`, cari `timezone`, lalu isi sesuai wilayahmu (misalnya `Asia/Jakarta` untuk WIB).

Pastikan `.env` tidak ikut ter-commit. File `.gitignore` bawaan Laravel sudah mengecualikannya; cek dengan `git status` nanti.

## Langkah 4 — Git

```bash
git init -b main
git add .
git commit -m "chore: inisialisasi proyek Laravel"
git checkout -b develop
```

Buat repositori **privat** di GitHub, lalu:

```bash
git remote add origin https://github.com/<username>/kipk.git
git push -u origin main
git push -u origin develop
```

Mulai dari sini, kerjakan fitur di cabang `feat/<nama>` (lihat `rules.md`).

## Langkah 5 — Salin dokumen proyek

Salin isi folder `kipk-project` ke root proyek Laravel agar semua rujukan jalur dokumen tetap benar:

```
kipk/
├── prd.md
├── techstack.md
├── design.md
├── rules.md
├── panduan-memulai.md
├── agents/            (agents.md, plan.md, task.md)
└── database/
    └── kipk_schema.sql   (folder database/ sudah ada di Laravel)
```

Lalu commit:

```bash
git add .
git commit -m "docs: tambah dokumen proyek (PRD, desain, rencana, tugas)"
```

Tandai T-002 sampai T-004 di `agents/task.md` setelah langkah 2 sampai 5 selesai.

## Langkah 6 — Paket tambahan (T-003)

```bash
composer require maatwebsite/excel barryvdh/laravel-dompdf
```

Paket Excel (3.1) mendukung Laravel hingga 13. Jika Composer melaporkan konflik versi, jangan paksa: salin pesannya dan tanyakan, karena biasanya cukup memilih versi paket yang cocok.

Opsional untuk pengolahan foto (dipakai di Fase 4):

```bash
composer require intervention/image
```

## Langkah 7 — Migrasi (T-005)

### 7a. Ubah migrasi `users` bawaan

Buka file `database/migrations/0001_01_01_000000_create_users_table.php`. Ganti isi pembuatan tabel `users` dan **hapus** `password_reset_tokens` (reset password dilakukan admin, bukan lewat email). Biarkan tabel `sessions` apa adanya.

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('username', 50)->unique();
    $table->string('password');
    $table->enum('role', ['admin', 'mahasiswa']);
    $table->boolean('aktif')->default(true);
    $table->rememberToken();
    $table->timestamps();
});
```

Perhatikan: kolom bernama `password` (bukan `password_hash` seperti di SQL), karena itu nama bawaan autentikasi Laravel. Penyimpangan ini sudah dicatat di `design.md`.

### 7b. Buat migrasi tabel lain

Buat **satu per satu, sesuai urutan ini**, karena foreign key membutuhkan tabel induk lebih dulu:

```bash
php artisan make:migration create_mahasiswa_table
php artisan make:migration create_semester_akademik_table
php artisan make:migration create_aturan_prestasi_table
php artisan make:migration create_peringatan_table
php artisan make:migration create_kegiatan_pembinaan_table
php artisan make:migration create_absensi_table
php artisan make:migration create_notifikasi_table
php artisan make:migration create_pengaturan_table
```

Pastikan urutan nama file di folder `database/migrations` mengikuti urutan di atas. Jika dua file memiliki awalan waktu yang sama, ganti sedikit awalannya.

Contoh isi migrasi `mahasiswa` sebagai pola:

```php
Schema::create('mahasiswa', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
    $table->string('nim', 20)->unique();
    $table->string('nama', 100);
    $table->string('prodi', 100);
    $table->unsignedSmallInteger('angkatan');
    $table->enum('status_kipk', ['aktif', 'cuti', 'lulus', 'dicabut', 'mengundurkan_diri'])
          ->default('aktif');
    $table->timestamps();
});
```

Untuk tabel lainnya, ikuti `database/kipk_schema.sql` (kolom, tipe, unique, dan foreign key). Hal yang mudah terlewat:

- `semester_akademik`: unik `(mahasiswa_id, semester_ke)`; `ips`/`ipk` dengan `decimal(3, 2)`.
- `absensi`: unik `(kegiatan_id, mahasiswa_id)`.
- `peringatan`: unik `(mahasiswa_id, semester_id)`.
- `pengaturan`: kunci utamanya `kunci` (string), bukan `id`.

Jalankan:

```bash
php artisan migrate
```

Jika ada galat foreign key, hampir pasti urutan migrasi salah. Perbaiki urutan lalu `php artisan migrate:fresh`.

## Langkah 8 — Model dan seeder

### 8a. Model

```bash
php artisan make:model Mahasiswa
php artisan make:model SemesterAkademik
php artisan make:model AturanPrestasi
php artisan make:model Peringatan
php artisan make:model KegiatanPembinaan
php artisan make:model Absensi
php artisan make:model Notifikasi
php artisan make:model Pengaturan
```

Karena nama tabel kita tunggal, setiap model perlu menyebutnya, contoh:

```php
class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';
    protected $fillable = ['user_id', 'nim', 'nama', 'prodi', 'angkatan', 'status_kipk'];

    public function user() { return $this->belongsTo(User::class); }
    public function semester() { return $this->hasMany(SemesterAkademik::class); }
}
```

Model `Pengaturan` juga perlu `protected $primaryKey = 'kunci'; public $incrementing = false; protected $keyType = 'string';`.

### 8b. Model `User`

Buka `app/Models/User.php` dan sesuaikan:

```php
protected $fillable = ['username', 'password', 'role', 'aktif'];
protected $hidden = ['password', 'remember_token'];

protected function casts(): array
{
    return ['password' => 'hashed', 'aktif' => 'boolean'];
}

public function mahasiswa() { return $this->hasOne(Mahasiswa::class); }
public function isAdmin(): bool { return $this->role === 'admin'; }
```

Hapus penyebutan `name`, `email`, dan `email_verified_at` jika masih ada. Bila `casts()` sudah memuat `'password' => 'hashed'`, kata sandi otomatis di-hash saat disimpan.

### 8c. Seeder (T-006)

Isi `database/seeders/DatabaseSeeder.php`:

```php
public function run(): void
{
    $kata = env('ADMIN_PASSWORD');
    if (! $kata) {
        throw new \RuntimeException('Isi ADMIN_PASSWORD di .env sebelum menjalankan seeder.');
    }

    // Ganti dengan username tiga admin sebenarnya
    foreach (['admin1', 'admin2', 'admin3'] as $username) {
        User::updateOrCreate(
            ['username' => $username],
            ['password' => $kata, 'role' => 'admin', 'aktif' => true]
        );
    }

    DB::table('aturan_prestasi')->updateOrInsert(
        ['berlaku_mulai' => '2026-01-01'],
        ['ipk_minimal' => 3.00]
    );

    $pengaturan = [
        'peringatan_batas_pencabutan' => '3',
    ];
    foreach ($pengaturan as $kunci => $nilai) {
        DB::table('pengaturan')->updateOrInsert(['kunci' => $kunci], ['nilai' => $nilai]);
    }
}
```

Jalankan:

```bash
php artisan db:seed
```

## Langkah 9 — Login dengan `username` dan pembatas peran (T-101, T-102)

### 9a. Controller

```bash
php artisan make:controller AuthController
```

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt([...$credentials, 'aktif' => true])) {
            $request->session()->regenerate();
            return redirect('/');
        }

        return back()
            ->withErrors(['username' => 'Username atau password salah.'])
            ->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
```

### 9b. Middleware peran

```bash
php artisan make:middleware EnsureRole
```

```php
public function handle(Request $request, Closure $next, string $role): Response
{
    if (! $request->user() || $request->user()->role !== $role) {
        abort(403);
    }
    return $next($request);
}
```

Daftarkan alias `role` di `bootstrap/app.php` pada bagian `withMiddleware` (bentuk persisnya mengikuti isi file di versimu):

```php
$middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
```

### 9c. Rute (`routes/web.php`)

```php
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }
    return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'mahasiswa.dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
});

Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::view('/dashboard', 'mahasiswa.dashboard')->name('dashboard');
});
```

Rute `/` sengaja mengarahkan berdasarkan peran supaya pengguna yang sudah login tidak berputar-putar antara halaman login dan beranda.

### 9d. Tampilan dasar (T-007)

Buat tiga berkas Blade.

`resources/views/layouts/app.blade.php`:

```blade
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'KIP-K')</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
      <span class="navbar-brand">KIP-K</span>
      @auth
        <form method="POST" action="{{ route('logout') }}" class="d-flex align-items-center gap-2">
          @csrf
          <span class="text-white small">{{ auth()->user()->username }}</span>
          <button class="btn btn-sm btn-light">Keluar</button>
        </form>
      @endauth
    </div>
  </nav>
  <main class="container">@yield('content')</main>
</body>
</html>
```

`resources/views/auth/login.blade.php`:

```blade
@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm"><div class="card-body">
      <h1 class="h4 mb-3">Masuk</h1>
      <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label" for="username">Username</label>
          <input id="username" name="username" value="{{ old('username') }}"
                 class="form-control @error('username') is-invalid @enderror" autofocus>
          @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label class="form-label" for="password">Kata sandi</label>
          <input id="password" name="password" type="password" class="form-control">
        </div>
        <button class="btn btn-primary w-100">Masuk</button>
      </form>
    </div></div>
  </div>
</div>
@endsection
```

`resources/views/admin/dashboard.blade.php` (salin untuk `mahasiswa/dashboard.blade.php` dengan judul "Dashboard Mahasiswa"):

```blade
@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
  <h1 class="h4">Dashboard Admin</h1>
  <p>Selamat datang, {{ auth()->user()->username }}.</p>
@endsection
```

## Langkah 10 — Jalankan dan uji

```bash
php artisan migrate:fresh --seed
php artisan serve
```

Buka `http://127.0.0.1:8000` (atau `http://kipk.test` bila memakai Laragon dengan host otomatis), lalu uji:

1. Buka `/` tanpa login: harus diarahkan ke halaman login.
2. Login sebagai `admin1` dengan `ADMIN_PASSWORD`: masuk ke dashboard admin.
3. Buat satu akun mahasiswa uji lewat Tinker:

```bash
php artisan tinker
>>> App\Models\User::create(['username' => 'mhs1', 'password' => 'rahasia123', 'role' => 'mahasiswa']);
```

4. Keluar, login sebagai `mhs1`: masuk ke dashboard mahasiswa.
5. Saat login sebagai `mhs1`, buka `/admin/dashboard`: harus tampil **403**.
6. Salah kata sandi lima kali dalam satu menit: percobaan berikutnya ditolak (pembatas login).

Setelah itu tulis tes otomatis (T-107):

```bash
php artisan make:test AksesPeranTest
```

```php
public function test_mahasiswa_tidak_bisa_membuka_halaman_admin(): void
{
    $user = User::create(['username' => 'mhs_uji', 'password' => 'rahasia123', 'role' => 'mahasiswa']);

    $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
}
```

Jalankan dengan `php artisan test`. Kalau tes ini memakai database sungguhan, tambahkan `use RefreshDatabase;` dan arahkan tes ke database terpisah (mis. `kipk_test`) di `phpunit.xml` agar data kerjamu tidak terhapus.

Rapikan kode dengan:

```bash
vendor\bin\pint
```

Commit, lalu centang tugas yang selesai di `agents/task.md`:

```bash
git add .
git commit -m "feat: login username, middleware peran, layout dasar (T-101, T-102, T-007)"
```

## Langkah 11 — Alur kerja harian

1. Buka `agents/task.md`, pilih tugas `[ ]` berikutnya (urut per fase).
2. Ubah menjadi `[~]`, buat cabang: `git checkout -b feat/<nama-tugas>`.
3. Baca bagian terkait di `prd.md` dan `design.md`.
4. Kerjakan, tulis tes, jalankan `php artisan test` dan `vendor\bin\pint`.
5. Commit dengan pesan `jenis: ringkasan (T-xxx)`, gabungkan ke `develop`, lalu ubah menjadi `[x]`.
6. Bila ada hal yang tidak jelas, catat di bagian "Catatan agent" di `task.md` dan tanyakan, jangan menebak aturan KIP-K.

Urutan fase selanjutnya: Fase 1 (CRUD penerima dan impor Excel), lalu Fase 2 (nilai dan peringatan).

## Masalah yang sering muncul

| Gejala | Penyebab dan perbaikan |
|---|---|
| `could not find driver` | Ekstensi `pdo_mysql` belum aktif; aktifkan lalu restart server |
| Composer gagal soal `ext-gd` atau `ext-zip` | Aktifkan ekstensi `gd` dan `zip` di PHP |
| `No application encryption key` | Jalankan `php artisan key:generate` |
| Galat foreign key saat `migrate` | Urutan migrasi salah; perbaiki lalu `php artisan migrate:fresh` |
| `Table 'kipk_db.mahasiswas' doesn't exist` | Model belum diberi `protected $table = 'mahasiswa';` |
| Halaman login berputar (redirect loop) | Pastikan rute `/` mengarah berdasarkan peran seperti di langkah 9c |
| Composer meminta versi PHP lebih tinggi | Perbarui PHP, atau pasang Laravel 12 (langkah 2, perintah alternatif) |
| Perubahan `.env` tidak terbaca | Jalankan `php artisan config:clear` |
