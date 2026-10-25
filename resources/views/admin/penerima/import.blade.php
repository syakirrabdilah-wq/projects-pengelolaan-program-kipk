@extends('layouts.app')
@section('title', 'Impor penerima')
@section('content')
<h1 class="h3 mb-3">Impor penerima</h1>
<div class="card mb-3"><div class="card-body">
    <p>Unggah XLSX, XLS, atau CSV berisi kolom: <code>nim</code>, <code>nama</code>, <code>prodi</code>, <code>angkatan</code>, <code>status_kipk</code> (opsional), dan <code>password</code>. NIM sebaiknya disimpan sebagai teks agar nol di depan tetap utuh.</p>
    <p>Nama pengguna akun dibuat dari NIM. Kata sandi minimal 8 karakter dan langsung disimpan dalam bentuk hash. Nama kolom untuk kata sandi pada berkas adalah <code>password</code>. Format status: aktif, cuti, lulus, dicabut, atau mengundurkan_diri. Baris berstatus <code>dicabut</code> wajib memiliki kolom <code>konfirmasi_pencabutan</code> bernilai <code>1</code> atau <code>yes</code>.</p>
    <form method="POST" action="{{ route('admin.penerima.import') }}" enctype="multipart/form-data" data-confirm="Proses impor penerima dari berkas ini?">
        @csrf
        <label class="form-label" for="file">Berkas impor (maks. 10 MB)</label>
        <input id="file" type="file" name="file" accept=".xlsx,.xls,.csv" data-file-name-target="nama-file-impor" required class="form-control @error('file') is-invalid @enderror">
        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="upload-file-summary mt-2" id="nama-file-impor" role="status">Belum ada berkas dipilih.</div>
        <button class="btn btn-primary mt-3">Proses impor</button>
        <a href="{{ route('admin.penerima.index') }}" class="btn btn-outline-secondary mt-3">Kembali</a>
    </form>
</div></div>
@if (session('hasilImpor'))
    <x-feedback-banner tone="info" title="Ringkasan impor" class="mb-3">Berhasil diimpor: {{ session('hasilImpor.berhasil') }} baris. Baris bermasalah: {{ count(session('hasilImpor.galat')) }}.</x-feedback-banner>
    @if (session('hasilImpor.galat'))
        <div class="card responsive-table-desktop"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Baris</th><th>Masalah</th></tr></thead><tbody>
            @foreach (session('hasilImpor.galat') as $galat)<tr><td>{{ $galat['baris'] }}</td><td>{{ implode(' ', $galat['pesan']) }}</td></tr>@endforeach
        </tbody></table></div></div>
        <div class="responsive-table-mobile">
            @foreach (session('hasilImpor.galat') as $galat)
                <article class="data-card"><div class="data-card-heading"><strong>Baris {{ $galat['baris'] }}</strong><x-status-badge status="ditolak" label="Perlu diperbaiki" /></div><p class="mb-0 mt-2">{{ implode(' ', $galat['pesan']) }}</p></article>
            @endforeach
        </div>
    @endif
@endif
@endsection
