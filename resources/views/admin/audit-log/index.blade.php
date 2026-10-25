@extends('layouts.app')
@section('title', 'Audit log')
@section('content')
<h1 class="h3 mb-2">Audit log</h1>
<p class="text-secondary">Riwayat perubahan nilai, status KIP-K, aturan prestasi, dan pengaturan.</p>
@if ($errors->any())<x-feedback-banner tone="important" title="Filter belum dapat diterapkan" class="mb-3">{{ $errors->first() }}</x-feedback-banner>@endif

<form method="GET" class="card card-body mb-3" role="search" aria-label="Filter audit log">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-sm-6 col-lg-2">
            <label class="form-label" for="filter-jenis">Jenis data</label>
            <select class="form-select" id="filter-jenis" name="jenis_data">
                <option value="">Semua jenis</option>
                @foreach (['semester_akademik' => 'Nilai semester', 'mahasiswa' => 'Status KIP-K', 'aturan_prestasi' => 'Aturan prestasi', 'pengaturan' => 'Pengaturan'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filter['jenis_data'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-lg-2">
            <label class="form-label" for="filter-aksi">Aksi</label>
            <select class="form-select" id="filter-aksi" name="aksi">
                <option value="">Semua aksi</option>
                @foreach (['dibuat' => 'Dibuat', 'diubah' => 'Diubah', 'dihapus' => 'Dihapus'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filter['aksi'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-lg-2">
            <label class="form-label" for="filter-aktor">Admin</label>
            <select class="form-select" id="filter-aktor" name="aktor">
                <option value="">Semua admin</option>
                @foreach ($adminList as $admin)
                    <option value="{{ $admin->username }}" @selected(($filter['aktor'] ?? '') === $admin->username)>{{ $admin->username }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-lg-2">
            <label class="form-label" for="filter-id">ID data</label>
            <input class="form-control" id="filter-id" name="data_id" value="{{ $filter['data_id'] ?? '' }}" inputmode="numeric">
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label" for="filter-dari">Dari</label>
            <input class="form-control" id="filter-dari" name="tanggal_dari" type="date" value="{{ $filter['tanggal_dari'] ?? '' }}">
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label" for="filter-sampai">Sampai</label>
            <input class="form-control" id="filter-sampai" name="tanggal_sampai" type="date" value="{{ $filter['tanggal_sampai'] ?? '' }}">
        </div>
        <div class="col-12 d-flex flex-wrap gap-2">
            <button class="btn btn-primary">Terapkan filter</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.audit-log.index') }}">Reset</a>
        </div>
    </div>
</form>

<div class="card responsive-table-desktop">
    <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead><tr><th>Waktu</th><th>Admin</th><th>Data</th><th>ID</th><th>Aksi</th><th>Perubahan</th></tr></thead>
            <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td class="text-nowrap">{{ $log->created_at->timezone(config('app.timezone'))->format('d-m-Y H:i:s') }}</td>
                    <td>{{ $log->pengguna?->username ?? 'Akun dihapus' }}</td>
                    <td>{{ str($log->jenis_data)->replace('_', ' ')->ucfirst() }}</td>
                    <td>{{ $log->data_id }}</td>
                    <td><x-status-badge :status="$log->aksi" /></td>
                    <td><ul class="audit-change-list mb-0">@foreach ($log->perubahan as $field => $values)<li><strong>{{ str($field)->replace('_', ' ') }}:</strong> {{ $values['dari'] ?? '—' }} → {{ $values['menjadi'] ?? '—' }}</li>@endforeach</ul></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state"><h3>Belum ada riwayat perubahan</h3><p class="text-secondary mb-0">Perubahan pada data penting akan tercatat di sini.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="responsive-table-mobile">
    @forelse ($logs as $log)
        <article class="data-card">
            <div class="data-card-heading"><strong>{{ str($log->jenis_data)->replace('_', ' ')->ucfirst() }} · ID {{ $log->data_id }}</strong><x-status-badge :status="$log->aksi" /></div>
            <dl class="row small">
                <dt class="col-4">Waktu</dt><dd class="col-8">{{ $log->created_at->timezone(config('app.timezone'))->format('d-m-Y H:i:s') }}</dd>
                <dt class="col-4">Admin</dt><dd class="col-8">{{ $log->pengguna?->username ?? 'Akun dihapus' }}</dd>
                <dt class="col-4">Perubahan</dt><dd class="col-8 mb-0"><ul class="audit-change-list mb-0">@foreach ($log->perubahan as $field => $values)<li><strong>{{ str($field)->replace('_', ' ') }}:</strong> {{ $values['dari'] ?? '—' }} → {{ $values['menjadi'] ?? '—' }}</li>@endforeach</ul></dd>
            </dl>
        </article>
    @empty
        <div class="data-card empty-state"><h3>Belum ada riwayat perubahan</h3><p class="text-secondary mb-0">Perubahan pada data penting akan tercatat di sini.</p></div>
    @endforelse
</div>

<div class="mt-3">{{ $logs->links() }}</div>
@endsection
