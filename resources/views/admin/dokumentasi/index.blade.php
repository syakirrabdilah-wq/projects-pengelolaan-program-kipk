@extends('layouts.app')
@section('title', 'Antrean verifikasi dokumentasi')
@section('content')
<h1 class="h3 mb-3">Antrean verifikasi dokumentasi</h1>
<div class="card responsive-table-desktop"><div class="table-responsive"><table class="table table-striped align-middle mb-0">
    <thead><tr><th>Mahasiswa</th><th>NIM</th><th>Minggu</th><th>Dikirim</th><th>Status tenggat</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($dokumentasi as $item)
        <tr>
            <td>{{ $item->mahasiswa->nama }}</td>
            <td>{{ $item->mahasiswa->nim }}</td>
            <td>{{ $item->minggu_ke }}, {{ $item->tahun }}</td>
            <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
            <td><x-status-badge :status="$item->terlambat ? 'terlambat' : 'tepat_waktu'" /></td>
            <td><a class="btn btn-sm btn-primary" href="{{ route('admin.dokumentasi.show', $item) }}">Tinjau</a></td>
        </tr>
    @empty
        <tr><td colspan="6"><div class="empty-state"><h3>Antrean dokumentasi kosong</h3><p class="text-secondary mb-0">Dokumentasi yang menunggu verifikasi akan tampil di sini.</p></div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="responsive-table-mobile">
    @forelse ($dokumentasi as $item)
        <article class="data-card">
            <div class="data-card-heading"><strong>{{ $item->mahasiswa->nama }}</strong><x-status-badge :status="$item->terlambat ? 'terlambat' : 'tepat_waktu'" /></div>
            <dl class="row small">
                <dt class="col-5">NIM</dt><dd class="col-7">{{ $item->mahasiswa->nim }}</dd>
                <dt class="col-5">Minggu</dt><dd class="col-7">{{ $item->minggu_ke }}, {{ $item->tahun }}</dd>
                <dt class="col-5">Dikirim</dt><dd class="col-7">{{ $item->created_at->format('d-m-Y H:i') }}</dd>
            </dl>
            <a class="btn btn-sm btn-primary" href="{{ route('admin.dokumentasi.show', $item) }}">Tinjau dokumentasi</a>
        </article>
    @empty
        <div class="data-card empty-state"><h3>Antrean dokumentasi kosong</h3><p class="text-secondary mb-0">Dokumentasi yang menunggu verifikasi akan tampil di sini.</p></div>
    @endforelse
</div>
<div class="mt-3">{{ $dokumentasi->links() }}</div>
@endsection
