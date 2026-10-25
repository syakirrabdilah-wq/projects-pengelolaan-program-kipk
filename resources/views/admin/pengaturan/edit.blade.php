@extends('layouts.app')
@section('title', 'Pengaturan')
@section('content')
<h1 class="h3 mb-3">Pengaturan</h1>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('admin.pengaturan.update') }}" data-confirm="Simpan pengaturan ini?">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label" for="dokumentasi_tenggat_hari">Hari tenggat dokumentasi</label>
            <select class="form-select @error('dokumentasi_tenggat_hari') is-invalid @enderror" id="dokumentasi_tenggat_hari" name="dokumentasi_tenggat_hari" required>
                @foreach ([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'] as $nomor => $hari)
                    <option value="{{ $nomor }}" @selected((int) old('dokumentasi_tenggat_hari', $pengaturan['dokumentasi_tenggat_hari']) === $nomor)>{{ $hari }}</option>
                @endforeach
            </select>
            @error('dokumentasi_tenggat_hari')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="dokumentasi_tenggat_jam">Jam tenggat</label>
            <input class="form-control @error('dokumentasi_tenggat_jam') is-invalid @enderror" id="dokumentasi_tenggat_jam" name="dokumentasi_tenggat_jam" type="time" value="{{ old('dokumentasi_tenggat_jam', $pengaturan['dokumentasi_tenggat_jam']) }}" required>
            @error('dokumentasi_tenggat_jam')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="peringatan_batas_pencabutan">Batas tingkat pencabutan</label>
            <input class="form-control @error('peringatan_batas_pencabutan') is-invalid @enderror" id="peringatan_batas_pencabutan" name="peringatan_batas_pencabutan" type="number" min="1" value="{{ old('peringatan_batas_pencabutan', $pengaturan['peringatan_batas_pencabutan']) }}" required>
            @error('peringatan_batas_pencabutan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Status KIP-K tidak berubah otomatis; admin tetap harus mengonfirmasi pencabutan.</div>
        </div>
        <button class="btn btn-primary">Simpan pengaturan</button>
    </form>
</div></div>
@endsection
