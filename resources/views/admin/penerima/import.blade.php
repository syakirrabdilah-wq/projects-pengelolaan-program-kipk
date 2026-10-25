@extends('layouts.app')
@section('title', 'Impor Penerima')
@section('content')
<h1 class="h3 mb-3">Impor Penerima</h1>
<div class="card mb-3"><div class="card-body">
    <p>Unggah XLSX, XLS, atau CSV berisi kolom: <code>nim</code>, <code>nama</code>, <code>prodi</code>, <code>angkatan</code>, <code>status_kipk</code> (opsional), dan <code>password</code>. NIM sebaiknya disimpan sebagai teks agar nol di depan tetap utuh.</p>
    <p>Username akun dibuat dari NIM. Password minimal 8 karakter dan langsung disimpan dalam bentuk hash. Format status: aktif, cuti, lulus, dicabut, atau mengundurkan_diri. Baris berstatus <code>dicabut</code> wajib memiliki kolom <code>konfirmasi_pencabutan</code> bernilai <code>1</code> atau <code>yes</code>.</p>
    <form method="POST" action="{{ route('admin.penerima.import') }}" enctype="multipart/form-data">
        @csrf
        <label class="form-label" for="file">Berkas impor (maks. 10 MB)</label>
        <input id="file" type="file" name="file" accept=".xlsx,.xls,.csv" required class="form-control @error('file') is-invalid @enderror">
        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <button class="btn btn-primary mt-3">Proses impor</button>
        <a href="{{ route('admin.penerima.index') }}" class="btn btn-outline-secondary mt-3">Kembali</a>
    </form>
</div></div>
@if (session('hasilImpor'))
    <div class="alert alert-info">Berhasil diimpor: {{ session('hasilImpor.berhasil') }} baris. Baris bermasalah: {{ count(session('hasilImpor.galat')) }}.</div>
    @if (session('hasilImpor.galat'))
        <div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Baris</th><th>Masalah</th></tr></thead><tbody>
            @foreach (session('hasilImpor.galat') as $galat)<tr><td>{{ $galat['baris'] }}</td><td>{{ implode(' ', $galat['pesan']) }}</td></tr>@endforeach
        </tbody></table></div></div>
    @endif
@endif
@endsection
