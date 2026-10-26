@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<div class="login-screen">
    <section class="login-story" aria-labelledby="welcome-title">
        <a class="login-brand-logo" href="/" aria-label="Pengelola KIP Kuliah Kab. Tegal">
            <img src="{{ asset('images/logo (1).png') }}" alt="Pengelola KIP Kuliah Kab. Tegal">
        </a>
        <svg class="login-illustration" viewBox="0 0 260 180" role="img" aria-label="Ilustrasi mahasiswa belajar dengan buku">
            <circle cx="130" cy="88" r="72" fill="#164c91"/>
            <path d="M42 151h176" stroke="#bfdbfe" stroke-width="4" stroke-linecap="round"/>
            <path d="M84 107c15-11 31-11 46 0v43c-15-11-31-11-46 0v-43Zm46 0c15-11 31-11 46 0v43c-15-11-31-11-46 0v-43Z" fill="#fff" stroke="#123b72" stroke-width="3"/>
            <path d="M96 119h22m-22 9h19m26-9h22m-22 9h19" stroke="#93b9e8" stroke-width="3" stroke-linecap="round"/>
            <circle cx="130" cy="57" r="20" fill="#f2c7a5"/><path d="M110 57c2-18 13-28 29-25 10 2 17 11 17 24-10-1-20-5-27-12-4 7-10 11-19 13Z" fill="#123b72"/>
            <path d="m101 42 29-15 30 15-30 15-29-15Z" fill="#f5ad24"/><path d="M112 79c5-8 11-12 18-12s14 4 19 12l7 28h-51l7-28Z" fill="#93b9e8"/>
        </svg>
        <h1 id="welcome-title">Selamat datang di ruang KIP-K</h1>
        <p>Sistem Manajemen Penerima KIP-K membantu Anda memantau perkembangan akademik dan pembinaan.</p>
    </section>
    <section class="login-panel" aria-labelledby="login-title">
        <div class="login-panel-inner">
            <p class="small text-success fw-bold mb-2">AKSES PENGGUNA</p>
            <h2 class="h3 mb-2" id="login-title">Masuk ke akun Anda</h2>
            <p class="text-secondary mb-4">Gunakan username dan kata sandi yang diberikan pengelola.</p>
            <form method="POST" action="{{ route('login.store') }}" class="d-grid gap-3">
                @csrf
                <div>
                    <label class="form-label" for="username">Nama pengguna</label>
                    <input class="form-control @error('username') is-invalid @enderror" id="username" name="username" type="text" value="{{ old('username') }}" maxlength="50" autocomplete="username" required autofocus>
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label" for="password">Kata sandi</label>
                    <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="current-password" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-primary" type="submit">Masuk</button>
            </form>
            <p class="small text-secondary mt-3 mb-0">Lupa kata sandi? Hubungi admin kemahasiswaan.</p>
        </div>
    </section>
</div>
@endsection
