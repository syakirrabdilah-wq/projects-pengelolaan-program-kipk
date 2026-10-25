@extends('layouts.app')
@section('title', 'Data penerima')
@section('content')
<div class="page-heading-row d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Data penerima</h1>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="{{ route('admin.penerima.import-form') }}">Impor Excel/CSV</a>
        <a class="btn btn-primary" href="{{ route('admin.penerima.create') }}">Tambah penerima</a>
    </div>
</div>
<form method="GET" class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-3"><label for="cari" class="form-label">Cari NIM atau nama</label><input id="cari" name="cari" value="{{ request('cari') }}" class="form-control"></div>
        <div class="col-12 col-md-3"><label for="prodi" class="form-label">Program studi</label><select id="prodi" name="prodi" class="form-select"><option value="">Semua prodi</option>@foreach ($prodiList as $prodi)<option value="{{ $prodi }}" @selected(request('prodi') === $prodi)>{{ $prodi }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><label for="angkatan" class="form-label">Angkatan</label><select id="angkatan" name="angkatan" class="form-select"><option value="">Semua</option>@foreach ($angkatanList as $angkatan)<option value="{{ $angkatan }}" @selected((string) request('angkatan') === (string) $angkatan)>{{ $angkatan }}</option>@endforeach</select></div>
        <div class="col-6 col-md-2"><label for="status_kipk_filter" class="form-label">Status</label><select id="status_kipk_filter" name="status_kipk" class="form-select"><option value="">Semua</option>@foreach ($statusList as $status)<option value="{{ $status }}" @selected(request('status_kipk') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>@endforeach</select></div>
        <div class="col-12 col-md-2 d-flex gap-2"><button class="btn btn-primary">Terapkan</button><a href="{{ route('admin.penerima.index') }}" class="btn btn-outline-secondary">Reset</a></div>
    </div>
</form>
<div class="card responsive-table-desktop"><div class="table-responsive"><table class="table table-striped mb-0 align-middle">
    <thead><tr><th>NIM</th><th>Nama</th><th>Program studi</th><th>Angkatan</th><th>Status KIP-K</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($penerima as $item)
        <tr><td>{{ $item->nim }}</td><td>{{ $item->nama }}</td><td>{{ $item->prodi }}</td><td>{{ $item->angkatan }}</td><td><x-status-badge :status="$item->status_kipk" /></td><td><a href="{{ route('admin.penerima.show', $item) }}">Lihat</a></td></tr>
    @empty
        <tr><td colspan="6"><div class="empty-state"><h3>Belum ada data penerima</h3><p class="text-secondary mb-0">Tambahkan penerima baru atau ubah filter pencarian.</p></div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="responsive-table-mobile">
    @forelse ($penerima as $item)
        <article class="data-card">
            <div class="data-card-heading"><strong>{{ $item->nama }}</strong><x-status-badge :status="$item->status_kipk" /></div>
            <dl class="row small">
                <dt class="col-4">NIM</dt><dd class="col-8">{{ $item->nim }}</dd>
                <dt class="col-4">Program studi</dt><dd class="col-8">{{ $item->prodi }}</dd>
                <dt class="col-4">Angkatan</dt><dd class="col-8">{{ $item->angkatan }}</dd>
            </dl>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.penerima.show', $item) }}">Lihat detail</a>
        </article>
    @empty
        <div class="data-card empty-state"><h3>Belum ada data penerima</h3><p class="text-secondary mb-0">Tambahkan penerima baru atau ubah filter pencarian.</p></div>
    @endforelse
</div>
<div class="mt-3">{{ $penerima->links() }}</div>
@endsection
