@extends('layouts.app')
@section('title', 'Data Penerima')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Data Penerima</h1>
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
<div class="card"><div class="table-responsive"><table class="table table-striped mb-0 align-middle">
    <thead><tr><th>NIM</th><th>Nama</th><th>Program studi</th><th>Angkatan</th><th>Status KIP-K</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($penerima as $item)
        <tr><td>{{ $item->nim }}</td><td>{{ $item->nama }}</td><td>{{ $item->prodi }}</td><td>{{ $item->angkatan }}</td><td>{{ str_replace('_', ' ', ucfirst($item->status_kipk)) }}</td><td><a href="{{ route('admin.penerima.show', $item) }}">Lihat</a></td></tr>
    @empty
        <tr><td colspan="6" class="text-center py-4">Belum ada data penerima.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $penerima->links() }}</div>
@endsection
