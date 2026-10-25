@csrf
<div class="mb-3">
    <label for="ipk_minimal" class="form-label">Batas IPK minimal</label>
    <input id="ipk_minimal" name="ipk_minimal" type="number" min="0" max="4" step="0.01" value="{{ old('ipk_minimal', $aturanPrestasi->ipk_minimal ?? '') }}" required class="form-control @error('ipk_minimal') is-invalid @enderror">
    @error('ipk_minimal')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="berlaku_mulai" class="form-label">Berlaku mulai</label>
    <input id="berlaku_mulai" name="berlaku_mulai" type="date" value="{{ old('berlaku_mulai', isset($aturanPrestasi) ? $aturanPrestasi->berlaku_mulai->format('Y-m-d') : '') }}" required class="form-control @error('berlaku_mulai') is-invalid @enderror">
    @error('berlaku_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<button class="btn btn-primary" onclick="return confirm('Simpan perubahan aturan prestasi?')">Simpan aturan</button>
<a class="btn btn-outline-secondary" href="{{ route('admin.aturan-prestasi.index') }}">Batal</a>
