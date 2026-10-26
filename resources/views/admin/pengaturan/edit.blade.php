@extends('layouts.app')
@section('title', 'Pengaturan')
@section('content')
<h1 class="h3 mb-3">Pengaturan</h1>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('admin.pengaturan.update') }}" data-confirm="Simpan pengaturan ini?">
        @csrf
        @method('PUT')
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
