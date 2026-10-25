@extends('layouts.app')
@section('title', 'Dokumentasi mingguan')
@section('content')
<h1 class="h3 mb-3">Dokumentasi mingguan</h1>
<section class="card mb-4" aria-labelledby="form-dokumentasi-title">
    <div class="card-body">
        <h2 id="form-dokumentasi-title" class="h5">Kirim dokumentasi minggu ke-{{ $nomorMingguIni }} tahun {{ $tahunIni }}</h2>
        @if ($mingguIni && $mingguIni->status_verifikasi !== 'ditolak')
            <p role="status">Dokumentasi minggu ini berstatus <x-status-badge :status="$mingguIni->status_verifikasi" />. Pengiriman baru tersedia kembali bila dokumentasi ditolak.</p>
        @else
            @if ($mingguIni?->status_verifikasi === 'ditolak')
                <x-feedback-banner tone="warning" title="Dokumentasi ditolak" class="mb-3">Anda dapat mengirim ulang dengan foto dan keterangan baru.</x-feedback-banner>
            @endif
            <form method="POST" action="{{ route('mahasiswa.dokumentasi.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="file_foto">Foto kegiatan (JPG atau PNG, maksimal 5 MB)</label>
                    <input class="form-control image-preview-input @error('file_foto') is-invalid @enderror" id="file_foto" name="file_foto" type="file" accept="image/jpeg,image/png" data-preview="pratinjau-foto" data-preview-label="label-pratinjau-foto" required>
                    @error('file_foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Pratinjau sebelum dikirim:</div>
                    <div class="upload-preview mt-2 p-2">
                        <span id="label-pratinjau-foto" class="small text-secondary">Pilih foto untuk melihat pratinjau.</span>
                        <img id="pratinjau-foto" class="img-fluid rounded d-none" alt="Pratinjau foto dokumentasi">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="keterangan">Keterangan (opsional)</label>
                    <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="3" maxlength="255">{{ old('keterangan') }}</textarea>
                    @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-primary">Kirim dokumentasi</button>
            </form>
        @endif
    </div>
</section>

<section aria-labelledby="riwayat-dokumentasi-title">
    <h2 id="riwayat-dokumentasi-title" class="h5">Riwayat dokumentasi</h2>
    <div class="row g-3">
        @forelse ($dokumentasi as $item)
            <div class="col-12 col-md-6">
                <article class="data-card h-100">
                    <div class="d-flex justify-content-between gap-2">
                        <h3 class="h6 mb-2">Minggu ke-{{ $item->minggu_ke }}, {{ $item->tahun }}</h3>
                        <x-status-badge :status="$item->status_verifikasi" />
                    </div>
                    @if ($item->terlambat)<p><x-status-badge status="terlambat" /></p>@endif
                    @if ($item->keterangan)<p>{{ $item->keterangan }}</p>@endif
                    @if ($item->catatan_admin)<p class="mb-2"><strong>Catatan admin:</strong> {{ $item->catatan_admin }}</p>@endif
                    <a href="{{ route('mahasiswa.dokumentasi.foto', $item) }}" target="_blank" rel="noopener">Lihat foto</a>
                </article>
            </div>
        @empty
            <div class="col-12"><div class="data-card empty-state"><h3>Belum ada dokumentasi</h3><p class="text-secondary mb-0">Dokumentasi yang dikirim akan tersimpan di sini.</p></div></div>
        @endforelse
    </div>
</section>
@endsection
