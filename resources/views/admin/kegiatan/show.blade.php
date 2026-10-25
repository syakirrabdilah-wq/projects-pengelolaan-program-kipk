@extends('layouts.app')
@section('title', 'Rekap kegiatan')
@section('content')
<div class="page-heading-row d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div><h1 class="h3">{{ $kegiatan->judul }}</h1><p class="text-muted mb-0">{{ $kegiatan->tanggal->format('d-m-Y') }} · Absen {{ $kegiatan->absen_buka->format('H:i') }}–{{ $kegiatan->absen_tutup->format('H:i') }}</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('admin.kegiatan.index') }}">Kembali</a>
</div>
<div class="card responsive-table-desktop"><div class="table-responsive"><table class="table table-striped align-middle mb-0">
    <thead><tr><th>Mahasiswa aktif</th><th>NIM</th><th>Status</th><th>Foto dan waktu diterima</th><th>Koreksi status</th></tr></thead>
    <tbody>
    @forelse ($mahasiswa as $item)
        @php($catatan = $item->absensi->first())
        <tr>
            <td><a href="{{ route('admin.kehadiran.mahasiswa', $item) }}">{{ $item->nama }}</a></td>
            <td>{{ $item->nim }}</td>
            <td><x-status-badge :status="$catatan?->status ?? 'tidak_hadir'" /></td>
            <td>
                @if ($catatan?->file_foto)
                    <a href="{{ route('admin.absensi.foto', $catatan) }}" target="_blank" rel="noopener">Lihat foto</a>
                    <div class="small text-muted">{{ $catatan->waktu->timezone(config('app.timezone'))->format('d-m-Y H:i:s') }} ({{ config('app.timezone') }})</div>
                @else
                    <span class="text-muted">Belum ada foto</span>
                @endif
            </td>
            <td>
                <form method="POST" enctype="multipart/form-data" action="{{ route('admin.kegiatan.absensi.koreksi', [$kegiatan, $item]) }}" class="d-grid gap-2" data-confirm="Simpan koreksi absensi {{ $item->nama }}?">
                    @csrf
                    <select class="form-select form-select-sm" name="status" aria-label="Status absensi {{ $item->nama }}">
                        @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'alpa' => 'Alpa'] as $nilai => $label)
                            <option value="{{ $nilai }}" @selected($catatan?->status === $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <label class="form-label small mb-0" for="foto-koreksi-{{ $item->id }}">Foto kegiatan (JPG/PNG, maks. 5 MB)</label>
                    <input class="form-control form-control-sm image-preview-input" id="foto-koreksi-{{ $item->id }}" type="file" name="file_foto" accept="image/jpeg,image/png" data-preview="pratinjau-koreksi-{{ $item->id }}" data-preview-label="label-koreksi-{{ $item->id }}" required>
                    @error('file_foto')<div class="small text-danger">{{ $message }}</div>@enderror
                    <div class="upload-preview p-2"><span id="label-koreksi-{{ $item->id }}" class="small text-secondary">Pilih foto untuk melihat pratinjau.</span><img id="pratinjau-koreksi-{{ $item->id }}" class="img-fluid rounded d-none" alt="Pratinjau foto koreksi"></div>
                    <button class="btn btn-sm btn-outline-primary">Simpan</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5"><div class="empty-state"><h3>Belum ada mahasiswa aktif</h3><p class="text-secondary mb-0">Mahasiswa yang mengikuti pembinaan akan tampil di sini.</p></div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="responsive-table-mobile">
    @forelse ($mahasiswa as $item)
        @php($catatan = $item->absensi->first())
        <article class="data-card">
            <div class="data-card-heading"><strong>{{ $item->nama }}</strong><x-status-badge :status="$catatan?->status ?? 'tidak_hadir'" /></div>
            <dl class="row small">
                <dt class="col-4">NIM</dt><dd class="col-8">{{ $item->nim }}</dd>
                <dt class="col-4">Bukti foto</dt><dd class="col-8">
                    @if ($catatan?->file_foto)
                        <a href="{{ route('admin.absensi.foto', $catatan) }}" target="_blank" rel="noopener">Lihat foto</a>
                        <div class="text-muted">{{ $catatan->waktu->timezone(config('app.timezone'))->format('d-m-Y H:i:s') }} ({{ config('app.timezone') }})</div>
                    @else
                        Belum tersedia
                    @endif
                </dd>
            </dl>
            <form method="POST" enctype="multipart/form-data" action="{{ route('admin.kegiatan.absensi.koreksi', [$kegiatan, $item]) }}" class="d-grid gap-2" data-confirm="Simpan koreksi absensi {{ $item->nama }}?">
                @csrf
                <span class="form-label mb-0">Koreksi status kehadiran</span>
                <select class="form-select" name="status" aria-label="Status absensi {{ $item->nama }}">
                    @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'alpa' => 'Alpa'] as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($catatan?->status === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="form-label small mb-0" for="foto-koreksi-mobile-{{ $item->id }}-file">Foto kegiatan (JPG/PNG, maks. 5 MB)</label>
                <input class="form-control image-preview-input" id="foto-koreksi-mobile-{{ $item->id }}-file" type="file" name="file_foto" accept="image/jpeg,image/png" data-preview="pratinjau-koreksi-mobile-{{ $item->id }}" data-preview-label="label-koreksi-mobile-{{ $item->id }}" required>
                @error('file_foto')<div class="small text-danger">{{ $message }}</div>@enderror
                <div class="upload-preview p-2"><span id="label-koreksi-mobile-{{ $item->id }}" class="small text-secondary">Pilih foto untuk melihat pratinjau.</span><img id="pratinjau-koreksi-mobile-{{ $item->id }}" class="img-fluid rounded d-none" alt="Pratinjau foto koreksi"></div>
                <button class="btn btn-outline-primary">Simpan koreksi</button>
            </form>
        </article>
    @empty
        <div class="data-card empty-state"><h3>Belum ada mahasiswa aktif</h3><p class="text-secondary mb-0">Mahasiswa yang mengikuti pembinaan akan tampil di sini.</p></div>
    @endforelse
</div>
@endsection
