@extends('layouts.app')
@section('title', 'Kegiatan pembinaan')
@section('content')
<div class="page-heading-row d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Kegiatan pembinaan</h1>
    <a class="btn btn-primary" href="{{ route('admin.kegiatan.create') }}">Tambah kegiatan</a>
</div>
<div class="card responsive-table-desktop"><div class="table-responsive"><table class="table table-striped align-middle mb-0">
    <thead><tr><th>Kegiatan</th><th>Tanggal</th><th>Jendela absen</th><th>Catatan masuk</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($kegiatan as $item)
        <tr>
            <td>{{ $item->judul }}</td>
            <td>{{ $item->tanggal->format('d-m-Y') }}</td>
            <td>{{ $item->absen_buka->format('d-m-Y H:i') }}–{{ $item->absen_tutup->format('H:i') }}</td>
            <td>{{ $item->absensi_count }}</td>
            <td class="d-flex flex-wrap gap-2">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.kegiatan.show', $item) }}">Rekap</a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.kegiatan.edit', $item) }}">Ubah</a>
                <form method="POST" action="{{ route('admin.kegiatan.destroy', $item) }}" data-confirm="Hapus kegiatan ini beserta catatan absensinya?">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5"><div class="empty-state"><h3>Belum ada kegiatan pembinaan</h3><p class="text-secondary mb-0">Tambahkan kegiatan untuk mulai mengelola jadwal dan kehadiran.</p></div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="responsive-table-mobile">
    @forelse ($kegiatan as $item)
        <article class="data-card">
            <div class="data-card-heading"><strong>{{ $item->judul }}</strong><span class="small text-secondary">{{ $item->absensi_count }} catatan masuk</span></div>
            <dl class="row small">
                <dt class="col-5">Tanggal</dt><dd class="col-7">{{ $item->tanggal->format('d-m-Y') }}</dd>
                <dt class="col-5">Jendela absen</dt><dd class="col-7">{{ $item->absen_buka->format('d-m-Y H:i') }}–{{ $item->absen_tutup->format('H:i') }}</dd>
            </dl>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.kegiatan.show', $item) }}">Rekap</a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.kegiatan.edit', $item) }}">Ubah</a>
                <form method="POST" action="{{ route('admin.kegiatan.destroy', $item) }}" data-confirm="Hapus kegiatan ini beserta catatan absensinya?">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </div>
        </article>
    @empty
        <div class="data-card empty-state"><h3>Belum ada kegiatan pembinaan</h3><p class="text-secondary mb-0">Tambahkan kegiatan untuk mulai mengelola jadwal dan kehadiran.</p></div>
    @endforelse
</div>
<div class="mt-3">{{ $kegiatan->links() }}</div>
@endsection
