@extends('layouts.app')
@section('title', 'Pembinaan')
@section('content')
<h1 class="h3 mb-3">Pembinaan</h1>
<div class="banner-sample banner-info mb-3" role="status"><span class="banner-symbol" aria-hidden="true">i</span><div><strong>Kehadiran {{ number_format($persentase, 1) }}%</strong><p class="mb-0">Dihitung dari {{ $jumlahSelesai }} kegiatan yang sudah selesai.</p></div></div>
<div class="row g-3">
@forelse ($kegiatan as $item)
    @php($catatan = $item->absensi->first())
    @php($jendela = $statusJendela[$item->id])
    <div class="col-12 col-md-6">
        <article class="card h-100"><div class="card-body">
            <h2 class="h5">{{ $item->judul }}</h2>
            <p class="mb-2">{{ $item->tanggal->format('d-m-Y') }}</p>
            <p class="text-muted">Absen {{ $item->absen_buka->format('H:i') }}–{{ $item->absen_tutup->format('H:i') }} ({{ config('app.timezone') }})</p>
            @if ($catatan)
                <p class="mb-0" role="status">Status: <x-status-badge :status="$catatan->status" /></p>
                <p class="text-muted mb-2">Foto diterima: {{ $catatan->waktu->timezone(config('app.timezone'))->format('d-m-Y H:i:s') }} ({{ config('app.timezone') }})</p>
                @if ($catatan->file_foto)
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('mahasiswa.absensi.foto', $catatan) }}" target="_blank" rel="noopener">Lihat foto absensi</a>
                @endif
            @elseif ($item->absen_tutup->lt(now()))
                <p class="mb-2" role="status">Status: <x-status-badge status="tidak_hadir" /></p>
                <button class="btn btn-secondary" disabled aria-describedby="alasan-{{ $item->id }}">Absen</button>
                <p class="form-text mt-2 mb-0" id="alasan-{{ $item->id }}">{{ $jendela['alasan'] }}</p>
            @else
                @if ($jendela['bisa_absen'])
                    <form method="POST" enctype="multipart/form-data" action="{{ route('mahasiswa.pembinaan.absen', $item) }}" class="d-grid gap-2">
                        @csrf
                        <label class="form-label mb-0" for="foto-absensi-{{ $item->id }}">Foto kegiatan (JPG/PNG, maks. 5 MB)</label>
                        <input class="form-control image-preview-input @error('file_foto') is-invalid @enderror" id="foto-absensi-{{ $item->id }}" data-preview="pratinjau-absensi-{{ $item->id }}" data-preview-label="label-absensi-{{ $item->id }}" type="file" name="file_foto" accept="image/jpeg,image/png" required>
                        @error('file_foto')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <div class="upload-preview p-2"><span id="label-absensi-{{ $item->id }}" class="small text-secondary">Pilih foto untuk melihat pratinjau.</span><img id="pratinjau-absensi-{{ $item->id }}" class="img-fluid rounded d-none" alt="Pratinjau foto absensi"></div>
                        <button class="btn btn-primary">Absen sekarang</button>
                    </form>
                @else
                    <button class="btn btn-secondary" disabled aria-describedby="alasan-{{ $item->id }}">Absen</button>
                    <p class="form-text mt-2 mb-0" id="alasan-{{ $item->id }}">{{ $jendela['alasan'] }}</p>
                @endif
            @endif
        </div></article>
    </div>
@empty
    <div class="col-12"><div class="data-card empty-state"><h3>Belum ada kegiatan pembinaan</h3><p class="text-secondary mb-0">Kegiatan yang dijadwalkan akan tampil di sini.</p></div></div>
@endforelse
</div>
@endsection
