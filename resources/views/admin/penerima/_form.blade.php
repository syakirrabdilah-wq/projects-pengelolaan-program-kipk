@csrf
<div class="mb-3">
    <label class="form-label" for="nim">NIM</label>
    <input id="nim" name="nim" value="{{ old('nim', $penerima->nim ?? '') }}" maxlength="20" required class="form-control @error('nim') is-invalid @enderror">
    @error('nim')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="nama">Nama</label>
    <input id="nama" name="nama" value="{{ old('nama', $penerima->nama ?? '') }}" maxlength="100" required class="form-control @error('nama') is-invalid @enderror">
    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="prodi">Program studi</label>
    <input id="prodi" name="prodi" value="{{ old('prodi', $penerima->prodi ?? '') }}" maxlength="100" required class="form-control @error('prodi') is-invalid @enderror">
    @error('prodi')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="angkatan">Angkatan</label>
    <input id="angkatan" name="angkatan" type="number" min="1901" max="2155" value="{{ old('angkatan', $penerima->angkatan ?? '') }}" required class="form-control @error('angkatan') is-invalid @enderror">
    @error('angkatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="status_kipk">Status KIP-K</label>
    <select id="status_kipk" name="status_kipk" required class="form-select @error('status_kipk') is-invalid @enderror">
        @foreach ($statusList as $status)
            <option value="{{ $status }}" @selected(old('status_kipk', $penerima->status_kipk ?? 'aktif') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
        @endforeach
    </select>
    @error('status_kipk')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3" id="konfirmasi-pencabutan" @class(['d-none' => old('status_kipk', $penerima->status_kipk ?? 'aktif') !== 'dicabut'])>
    <div class="form-check">
        <input id="konfirmasi_pencabutan" name="konfirmasi_pencabutan" type="checkbox" value="1" @checked(old('konfirmasi_pencabutan')) class="form-check-input @error('konfirmasi_pencabutan') is-invalid @enderror">
        <label class="form-check-label" for="konfirmasi_pencabutan">Saya mengonfirmasi status pencabutan KIP-K</label>
        @error('konfirmasi_pencabutan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>
<div class="mb-3">
    <label class="form-label" for="password">{{ isset($penerima) ? 'Kata sandi baru (opsional)' : 'Kata sandi awal akun' }}</label>
    <input id="password" name="password" type="password" minlength="8" @required(! isset($penerima)) class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<button class="btn btn-primary">Simpan</button>
<a href="{{ route('admin.penerima.index') }}" class="btn btn-outline-secondary">Batal</a>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.getElementById('status_kipk').addEventListener('change', function () {
        document.getElementById('konfirmasi-pencabutan').classList.toggle('d-none', this.value !== 'dicabut');
    });
</script>
