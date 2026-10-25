<form method="POST" action="{{ $aksi }}" class="card card-body" data-confirm="{{ $method === 'PUT' ? 'Simpan perubahan kegiatan?' : 'Simpan jadwal kegiatan?' }}">
    @csrf
    @if ($method === 'PUT') @method('PUT') @endif
    <div class="mb-3">
        <label class="form-label" for="judul">Judul kegiatan</label>
        <input class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" maxlength="150" value="{{ old('judul', $kegiatan->judul ?? '') }}" required>
        @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label class="form-label" for="tanggal">Tanggal kegiatan</label>
        <input class="form-control @error('tanggal') is-invalid @enderror" id="tanggal" name="tanggal" type="date" value="{{ old('tanggal', isset($kegiatan) ? $kegiatan->tanggal->format('Y-m-d') : '') }}" required>
        @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="row">
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label" for="absen_buka">Absen dibuka</label>
            <input class="form-control @error('absen_buka') is-invalid @enderror" id="absen_buka" name="absen_buka" type="datetime-local" value="{{ old('absen_buka', isset($kegiatan) ? $kegiatan->absen_buka->format('Y-m-d\TH:i') : '') }}" required>
            @error('absen_buka')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label" for="absen_tutup">Absen ditutup</label>
            <input class="form-control @error('absen_tutup') is-invalid @enderror" id="absen_tutup" name="absen_tutup" type="datetime-local" value="{{ old('absen_tutup', isset($kegiatan) ? $kegiatan->absen_tutup->format('Y-m-d\TH:i') : '') }}" required>
            @error('absen_tutup')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Waktu tutup harus setelah waktu buka. Waktu mengikuti zona {{ config('app.timezone') }}.</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary">Simpan kegiatan</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.kegiatan.index') }}">Batal</a>
    </div>
</form>
