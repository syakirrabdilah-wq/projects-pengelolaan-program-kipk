@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
<h1 class="h3 mb-3">Laporan</h1>
<p>Ekspor hanya memuat data akademik dan administrasi yang diperlukan. Berkas KHS dan foto privat tidak disertakan.</p>

<div class="row g-3">
    @foreach ([['nilai', 'Rekap nilai'], ['kehadiran', 'Rekap kehadiran'], ['dokumentasi', 'Rekap dokumentasi']] as [$jenis, $judul])
        <div class="col-12 col-lg-4">
            <section class="card h-100">
                <div class="card-body">
                    <h2 class="h5">{{ $judul }}</h2>
                    <form method="GET" action="{{ route('admin.laporan.excel', $jenis) }}" class="d-grid gap-3">
                        <div>
                            <label class="form-label" for="{{ $jenis }}-prodi">Program studi</label>
                            <select class="form-select" id="{{ $jenis }}-prodi" name="prodi">
                                <option value="">Semua program studi</option>
                                @foreach ($prodi as $opsi)<option value="{{ $opsi }}" @selected(($filter['prodi'] ?? '') === $opsi)>{{ $opsi }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="{{ $jenis }}-angkatan">Angkatan</label>
                            <select class="form-select" id="{{ $jenis }}-angkatan" name="angkatan">
                                <option value="">Semua angkatan</option>
                                @foreach ($angkatan as $opsi)<option value="{{ $opsi }}" @selected((string) ($filter['angkatan'] ?? '') === (string) $opsi)>{{ $opsi }}</option>@endforeach
                            </select>
                        </div>
                        @if ($jenis === 'nilai')
                            <div>
                                <label class="form-label" for="nilai-semester">Semester</label>
                                <select class="form-select" id="nilai-semester" name="semester">
                                    <option value="">Semua semester</option>
                                    @foreach ($semester as $opsi)<option value="{{ $opsi }}" @selected((string) ($filter['semester'] ?? '') === (string) $opsi)>Semester {{ $opsi }}</option>@endforeach
                                </select>
                            </div>
                        @endif
                        <button class="btn btn-primary mt-auto">Unduh Excel</button>
                    </form>
                </div>
            </section>
        </div>
    @endforeach
</div>

<section class="card mt-3">
    <div class="card-body">
        <h2 class="h5">Laporan nilai per semester (PDF)</h2>
        <form method="GET" action="{{ route('admin.laporan.pdf-semester') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label" for="pdf-semester">Semester</label>
                <select class="form-select" id="pdf-semester" name="semester" required>
                    <option value="">Pilih semester</option>
                    @foreach ($semester as $opsi)<option value="{{ $opsi }}">Semester {{ $opsi }}</option>@endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="pdf-prodi">Program studi</label>
                <select class="form-select" id="pdf-prodi" name="prodi">
                    <option value="">Semua program studi</option>
                    @foreach ($prodi as $opsi)<option value="{{ $opsi }}">{{ $opsi }}</option>@endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label" for="pdf-angkatan">Angkatan</label>
                <select class="form-select" id="pdf-angkatan" name="angkatan">
                    <option value="">Semua angkatan</option>
                    @foreach ($angkatan as $opsi)<option value="{{ $opsi }}">{{ $opsi }}</option>@endforeach
                </select>
            </div>
            <div class="col-12 col-md-1"><button class="btn btn-outline-primary w-100">PDF</button></div>
        </form>
    </div>
</section>
@endsection
