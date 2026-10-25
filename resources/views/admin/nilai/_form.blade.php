@csrf
<div class="mb-3">
    <label class="form-label" for="semester_ke">Semester ke</label>
    <input id="semester_ke" name="semester_ke" type="number" min="1" max="255" value="{{ old('semester_ke', $semester->semester_ke ?? '') }}" required class="form-control @error('semester_ke') is-invalid @enderror">
    @error('semester_ke')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="ips">IPS</label>
    <input id="ips" name="ips" type="number" min="0" max="4" step="0.01" value="{{ old('ips', $semester->ips ?? '') }}" required class="form-control @error('ips') is-invalid @enderror">
    @error('ips')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="ipk">IPK</label>
    <input id="ipk" name="ipk" type="number" min="0" max="4" step="0.01" value="{{ old('ipk', $semester->ipk ?? '') }}" required class="form-control @error('ipk') is-invalid @enderror">
    @error('ipk')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="sks">SKS</label>
    <input id="sks" name="sks" type="number" min="0" max="65535" value="{{ old('sks', $semester->sks ?? '') }}" required class="form-control @error('sks') is-invalid @enderror">
    @error('sks')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label" for="file_khs">KHS PDF (maks. 5 MB) {{ isset($semester) && $semester->file_khs ? '— kosongkan jika tidak mengganti' : '' }}</label>
    <input id="file_khs" name="file_khs" type="file" accept="application/pdf,.pdf" class="form-control @error('file_khs') is-invalid @enderror">
    @error('file_khs')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<button class="btn btn-primary" onclick="return confirm('Simpan perubahan nilai semester ini?')">Simpan nilai</button>
<a class="btn btn-outline-secondary" href="{{ route('admin.penerima.nilai.index', $penerima) }}">Batal</a>
