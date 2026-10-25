@extends('layouts.app')
@section('title', 'Tinjau dokumentasi')
@section('content')
<h1 class="h3">Tinjau dokumentasi</h1>
<p>{{ $dokumentasi->mahasiswa->nama }} · {{ $dokumentasi->mahasiswa->nim }}</p>
<p>Minggu ke-{{ $dokumentasi->minggu_ke }}, {{ $dokumentasi->tahun }} · dikirim {{ $dokumentasi->created_at->format('d-m-Y H:i') }}</p>
<x-status-badge :status="$dokumentasi->terlambat ? 'terlambat' : 'tepat_waktu'" />
@if ($dokumentasi->keterangan)<p>{{ $dokumentasi->keterangan }}</p>@endif
<a href="{{ route('admin.dokumentasi.foto', $dokumentasi) }}" target="_blank" rel="noopener">
    <img class="img-fluid rounded mb-3 document-review-image" src="{{ route('admin.dokumentasi.foto', $dokumentasi) }}" alt="Foto dokumentasi dari {{ $dokumentasi->mahasiswa->nama }}">
</a>
@if ($dokumentasi->status_verifikasi === 'menunggu')
    <form method="POST" action="{{ route('admin.dokumentasi.verifikasi', $dokumentasi) }}" class="card card-body" data-confirm="Simpan keputusan verifikasi dokumentasi?">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="catatan_admin">Catatan</label>
            <textarea class="form-control @error('catatan_admin') is-invalid @enderror" id="catatan_admin" name="catatan_admin" rows="3" maxlength="200" required>{{ old('catatan_admin') }}</textarea>
            @error('catatan_admin')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        @error('status_verifikasi')<x-feedback-banner tone="important" class="mb-3">{{ $message }}</x-feedback-banner>@enderror
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-success" name="status_verifikasi" value="diterima">Terima</button>
            <button class="btn btn-danger" name="status_verifikasi" value="ditolak">Tolak</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.dokumentasi.index') }}">Kembali</a>
        </div>
    </form>
@else
    <p role="status">Status: <x-status-badge :status="$dokumentasi->status_verifikasi" /></p>
    @if ($dokumentasi->catatan_admin)<p>Catatan: {{ $dokumentasi->catatan_admin }}</p>@endif
    <a class="btn btn-outline-secondary" href="{{ route('admin.dokumentasi.index') }}">Kembali ke antrean</a>
@endif
@endsection
