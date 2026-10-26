<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KIP-K')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|merriweather:400,700" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/kipk-theme.css') }}" rel="stylesheet">
</head>
<body class="{{ request()->routeIs('login') ? 'bg-white' : 'bg-light' }}">
@unless (request()->routeIs('login'))
<nav class="navbar navbar-dark bg-primary mb-4 app-navbar">
    <div class="container app-navbar-inner">
        <a class="navbar-brand app-brand app-brand-logo me-0" href="/" aria-label="Pengelola KIP Kuliah Kab. Tegal">
            <img src="{{ asset('images/logo (1).png') }}" alt="Pengelola KIP Kuliah Kab. Tegal">
        </a>
        @auth
            <nav class="app-nav-links" aria-label="Navigasi utama">
                @if (auth()->user()->role === 'admin')
                    <a @class(['app-nav-link', 'is-active' => request()->routeIs('admin.dashboard')]) href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>Dashboard</a>
                    <a @class(['app-nav-link', 'is-active' => request()->routeIs('admin.penerima.*')]) href="{{ route('admin.penerima.index') }}" @if (request()->routeIs('admin.penerima.*')) aria-current="page" @endif>Penerima &amp; nilai</a>
                    <a @class(['app-nav-link', 'is-active' => request()->routeIs('admin.aturan-prestasi.*')]) href="{{ route('admin.aturan-prestasi.index') }}" @if (request()->routeIs('admin.aturan-prestasi.*')) aria-current="page" @endif>Aturan prestasi</a>
                    <a @class(['app-nav-link', 'is-active' => request()->routeIs('admin.peringatan.*')]) href="{{ route('admin.peringatan.index') }}" @if (request()->routeIs('admin.peringatan.*')) aria-current="page" @endif>Peringatan</a>
                    <a @class(['app-nav-link', 'is-active' => request()->routeIs('admin.kegiatan.*')]) href="{{ route('admin.kegiatan.index') }}" @if (request()->routeIs('admin.kegiatan.*')) aria-current="page" @endif>Pembinaan</a>
                    <a @class(['app-nav-link', 'is-active' => request()->routeIs('admin.laporan.*')]) href="{{ route('admin.laporan.index') }}" @if (request()->routeIs('admin.laporan.*')) aria-current="page" @endif>Laporan</a>
                    <a @class(['app-nav-link', 'is-active' => request()->routeIs('admin.pengaturan.*')]) href="{{ route('admin.pengaturan.edit') }}" @if (request()->routeIs('admin.pengaturan.*')) aria-current="page" @endif>Pengaturan</a>
                    <a @class(['app-nav-link', 'is-active' => request()->routeIs('admin.akun.*')]) href="{{ route('admin.akun.index') }}" @if (request()->routeIs('admin.akun.*')) aria-current="page" @endif>Manajemen akun</a>
                    <a @class(['app-nav-link', 'is-active' => request()->routeIs('admin.audit-log.*')]) href="{{ route('admin.audit-log.index') }}" @if (request()->routeIs('admin.audit-log.*')) aria-current="page" @endif>Audit log</a>
                @elseif (auth()->user()->role === 'mahasiswa')
                    <a @class(['app-nav-link', 'is-active' => request()->routeIs('mahasiswa.dashboard')]) href="{{ route('mahasiswa.dashboard') }}" @if (request()->routeIs('mahasiswa.dashboard')) aria-current="page" @endif>Beranda</a>
                    <a @class(['app-nav-link', 'is-active' => request()->routeIs('mahasiswa.nilai.*')]) href="{{ route('mahasiswa.nilai.index') }}" @if (request()->routeIs('mahasiswa.nilai.*')) aria-current="page" @endif>Nilai saya</a>
                    <a @class(['app-nav-link', 'is-active' => request()->routeIs('mahasiswa.pembinaan.*')]) href="{{ route('mahasiswa.pembinaan.index') }}" @if (request()->routeIs('mahasiswa.pembinaan.*')) aria-current="page" @endif>Pembinaan</a>
                @endif
                <a @class(['app-nav-link', 'is-active' => request()->routeIs('notifikasi.*')]) href="{{ route('notifikasi.index') }}" @if (request()->routeIs('notifikasi.*')) aria-current="page" @endif>
                    Notifikasi @if ($jumlahNotifikasiBelumDibaca > 0)<span class="badge text-bg-light text-primary">{{ $jumlahNotifikasiBelumDibaca }} belum dibaca</span>@endif
                </a>
            </nav>
            <form method="POST" action="{{ route('logout') }}" @class(['app-logout-form', 'app-logout-form--admin' => auth()->user()->role === 'admin'])>
                @csrf
                <button class="btn btn-sm btn-outline-light" type="submit">
                    <svg aria-hidden="true" viewBox="0 0 20 20" width="17" height="17" fill="none">
                        <path d="M8 3.5H4.75A1.25 1.25 0 0 0 3.5 4.75v10.5A1.25 1.25 0 0 0 4.75 16.5H8m3-3 3.5-3.5L11 6.5m3.5 3.5H7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        @endauth
    </div>
</nav>
@endunless
<main class="{{ request()->routeIs('login') ? 'container-fluid px-0 pb-0' : 'container pb-5' }}">
    <div class="app-page">
        @if (session('sukses'))<x-feedback-banner tone="success" class="mb-3">{{ session('sukses') }}</x-feedback-banner>@endif
        @if ($errors->has('absensi'))<x-feedback-banner tone="important" title="Absensi belum tersimpan" class="mb-3">{{ $errors->first('absensi') }}</x-feedback-banner>@endif
        @yield('content')
    </div>
</main>
<footer class="app-footer bg-white">
    <div class="container d-flex flex-column align-items-center justify-content-center gap-2 text-center">
        <span>Bagian Kemahasiswaan · Tahun Akademik 2026/2027</span>
    </div>
</footer>
<dialog class="confirmation-dialog" id="confirmation-dialog" aria-labelledby="confirmation-dialog-title" aria-describedby="confirmation-dialog-message">
    <form method="dialog">
        <h2 id="confirmation-dialog-title">Konfirmasi tindakan</h2>
        <p id="confirmation-dialog-message"></p>
        <div class="confirmation-dialog-actions">
            <button class="btn btn-outline-secondary" type="button" data-confirm-cancel>Batal</button>
            <button class="btn btn-primary" type="button" data-confirm-accept>Lanjutkan</button>
        </div>
    </form>
</dialog>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    const confirmationDialog = document.getElementById('confirmation-dialog');
    const confirmationMessage = document.getElementById('confirmation-dialog-message');
    const confirmationAccept = confirmationDialog.querySelector('[data-confirm-accept]');
    let pendingConfirmation = null;

    document.addEventListener('submit', function (event) {
        const form = event.target;
        const pesan = form.getAttribute('data-confirm');
        if (!pesan) return;

        if (typeof confirmationDialog.showModal !== 'function') {
            if (!window.confirm(pesan)) event.preventDefault();
            return;
        }

        event.preventDefault();
        pendingConfirmation = { form, submitter: event.submitter };
        confirmationMessage.textContent = pesan;
        confirmationDialog.showModal();
    });

    document.querySelector('[data-confirm-cancel]').addEventListener('click', function () {
        pendingConfirmation = null;
        confirmationDialog.close();
    });

    confirmationDialog.addEventListener('close', function () {
        pendingConfirmation = null;
    });

    confirmationAccept.addEventListener('click', function () {
        if (!pendingConfirmation) return;
        const { form, submitter } = pendingConfirmation;
        form.removeAttribute('data-confirm');
        confirmationDialog.close();
        if (submitter) form.requestSubmit(submitter);
        else form.requestSubmit();
    });

    document.addEventListener('change', function (event) {
        const input = event.target.closest('.image-preview-input');
        if (!input) return;

        const preview = document.getElementById(input.dataset.preview);
        const label = document.getElementById(input.dataset.previewLabel);
        if (!preview || !label) return;
        if (preview.dataset.url) {
            URL.revokeObjectURL(preview.dataset.url);
            delete preview.dataset.url;
        }

        const file = input.files?.[0];
        if (!file || !['image/jpeg', 'image/png'].includes(file.type)) {
            preview.classList.add('d-none');
            preview.removeAttribute('src');
            label.classList.remove('d-none');
            return;
        }

        const url = URL.createObjectURL(file);
        preview.dataset.url = url;
        preview.src = url;
        preview.classList.remove('d-none');
        label.classList.add('d-none');
    });

    document.addEventListener('change', function (event) {
        const input = event.target.closest('[data-file-name-target]');
        if (!input) return;
        const label = document.getElementById(input.dataset.fileNameTarget);
        if (!label) return;
        const file = input.files?.[0];
        label.textContent = file ? `${file.name} - ${Math.ceil(file.size / 1024)} KB siap diunggah` : 'Belum ada berkas dipilih.';
    });
</script>
</body>
</html>
