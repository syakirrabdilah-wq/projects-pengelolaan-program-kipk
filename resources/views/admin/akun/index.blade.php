@extends('layouts.app')
@section('title', 'Manajemen akun')
@section('content')
<h1 class="h3 mb-3">Manajemen akun</h1>
<p class="text-secondary">Atur ulang kata sandi atau ubah status akun admin dan mahasiswa.</p>

@error('akun')<x-feedback-banner tone="important" title="Akun belum diperbarui" class="mb-3">{{ $message }}</x-feedback-banner>@enderror

<form method="GET" class="row g-2 mb-3" role="search">
    <div class="col-sm-6 col-md-5">
        <label class="form-label" for="cari-akun">Cari akun</label>
        <input class="form-control" id="cari-akun" name="cari" value="{{ request('cari') }}" placeholder="Nama pengguna, NIM, atau nama">
    </div>
    <div class="col-sm-4 col-md-3">
        <label class="form-label" for="filter-role">Peran</label>
        <select class="form-select" id="filter-role" name="role">
            <option value="">Semua peran</option>
            <option value="admin" @selected(request('role') === 'admin')>Admin</option>
            <option value="mahasiswa" @selected(request('role') === 'mahasiswa')>Mahasiswa</option>
        </select>
    </div>
    <div class="col-sm-2 d-flex align-items-end"><button class="btn btn-outline-primary">Cari</button></div>
</form>

<div class="responsive-table-desktop table-responsive">
    <table class="table table-striped align-middle">
        <thead><tr><th>Nama pengguna</th><th>Nama</th><th>Peran</th><th>Status akun</th><th>Kata sandi baru</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse ($akun as $user)
            <tr>
                <td>{{ $user->username }}</td>
                <td>{{ $user->mahasiswa?->nama ?? '—' }}</td>
                <td>{{ $user->isAdmin() ? 'Admin' : 'Mahasiswa' }}</td>
                <td><x-status-badge :status="$user->aktif ? 'aktif' : 'nonaktif'" /></td>
                <td>
                    <form method="POST" action="{{ route('admin.akun.password', $user) }}" class="d-flex flex-column flex-lg-row gap-2" data-confirm="Atur ulang kata sandi akun {{ $user->username }}?">
                        @csrf @method('PUT')
                        <label class="visually-hidden" for="password-{{ $user->id }}">Kata sandi baru untuk {{ $user->username }}</label>
                        <input class="form-control form-control-sm" id="password-{{ $user->id }}" type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Kata sandi baru" required>
                        <label class="visually-hidden" for="password-confirm-{{ $user->id }}">Konfirmasi kata sandi baru</label>
                        <input class="form-control form-control-sm" id="password-confirm-{{ $user->id }}" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" placeholder="Ulangi kata sandi" required>
                        <button class="btn btn-sm btn-outline-primary text-nowrap">Atur ulang</button>
                    </form>
                </td>
                <td>
                    @if ($user->is(auth()->user()))
                        <span class="small text-secondary">Akun Anda</span>
                    @else
                        <form method="POST" action="{{ route('admin.akun.status', $user) }}" data-confirm="{{ $user->aktif ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $user->username }}?">
                            @csrf @method('PUT')
                            <button class="btn btn-sm {{ $user->aktif ? 'btn-outline-danger' : 'btn-outline-success' }}">{{ $user->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center py-4">Tidak ada akun yang cocok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="responsive-table-mobile">
    @forelse ($akun as $user)
        <article class="data-card">
            <div class="data-card-heading"><strong>{{ $user->mahasiswa?->nama ?? $user->username }}</strong><x-status-badge :status="$user->aktif ? 'aktif' : 'nonaktif'" /></div>
            <dl class="row small">
                <dt class="col-5">Nama pengguna</dt><dd class="col-7">{{ $user->username }}</dd>
                <dt class="col-5">Peran</dt><dd class="col-7">{{ $user->isAdmin() ? 'Admin' : 'Mahasiswa' }}</dd>
            </dl>
            <div class="d-grid gap-2">
                <form method="POST" action="{{ route('admin.akun.password', $user) }}" class="d-grid gap-2" data-confirm="Atur ulang kata sandi akun {{ $user->username }}?">
                    @csrf @method('PUT')
                    <label class="visually-hidden" for="password-mobile-{{ $user->id }}">Kata sandi baru untuk {{ $user->username }}</label>
                    <input class="form-control" id="password-mobile-{{ $user->id }}" type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Kata sandi baru" required>
                    <label class="visually-hidden" for="password-confirm-mobile-{{ $user->id }}">Konfirmasi kata sandi baru</label>
                    <input class="form-control" id="password-confirm-mobile-{{ $user->id }}" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" placeholder="Ulangi kata sandi" required>
                    <button class="btn btn-outline-primary">Atur ulang kata sandi</button>
                </form>
                @if ($user->is(auth()->user()))
                    <span class="small text-secondary">Akun Anda tidak dapat dinonaktifkan.</span>
                @else
                    <form method="POST" action="{{ route('admin.akun.status', $user) }}" data-confirm="{{ $user->aktif ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $user->username }}?">
                        @csrf @method('PUT')
                        <button class="btn {{ $user->aktif ? 'btn-outline-danger' : 'btn-outline-success' }} w-100">{{ $user->aktif ? 'Nonaktifkan akun' : 'Aktifkan akun' }}</button>
                    </form>
                @endif
            </div>
        </article>
    @empty
        <div class="data-card empty-state"><h3>Tidak ada akun yang cocok</h3><p class="text-secondary mb-0">Ubah kata pencarian atau pilih peran lain.</p></div>
    @endforelse
</div>
{{ $akun->links() }}
@endsection
