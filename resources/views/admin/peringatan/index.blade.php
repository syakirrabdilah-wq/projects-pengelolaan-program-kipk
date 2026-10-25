@extends('layouts.app')
@section('title', 'Peringatan IPK')
@section('content')
<h1 class="h3 mb-3">Peringatan IPK</h1>
@if (session('info'))<x-feedback-banner tone="info" class="mb-3">{{ session('info') }}</x-feedback-banner>@endif
@if ($errors->has('pencabutan'))<x-feedback-banner tone="important" title="Pencabutan belum diproses" class="mb-3">{{ $errors->first('pencabutan') }}</x-feedback-banner>@endif
<p class="text-muted">Ambang pencabutan saat ini: tingkat {{ $batasPencabutan }}.</p>
@php($kelompokPeringatan = $peringatan->getCollection()->groupBy('tingkat'))
@forelse ($kelompokPeringatan as $tingkat => $daftar)
    <section class="mb-4" aria-labelledby="tingkat-{{ $tingkat }}">
        <h2 id="tingkat-{{ $tingkat }}" class="h5">Tingkat {{ $tingkat }}
            @if ($tingkat >= $batasPencabutan)<x-status-badge status="pencabutan" label="Memenuhi ambang pencabutan" />@endif
        </h2>
        <div class="card responsive-table-desktop"><div class="table-responsive"><table class="table table-striped align-middle mb-0">
            <thead><tr><th>Mahasiswa</th><th>NIM</th><th>Semester</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach ($daftar as $item)
                <tr>
                    <td>{{ $item->mahasiswa->nama }}</td>
                    <td>{{ $item->mahasiswa->nim }}</td>
                    <td>{{ $item->semester->semester_ke }}</td>
                    <td>{{ $item->tanggal->format('d-m-Y') }}</td>
                    <td>
                        @if ($item->tingkat >= $batasPencabutan)
                            <x-status-badge status="pencabutan" />
                        @else
                            <x-status-badge status="evaluasi" />
                        @endif
                        <x-status-badge :status="$item->mahasiswa->status_kipk" />
                    </td>
                    <td>
                        @if ($item->tingkat >= $batasPencabutan && $item->mahasiswa->status_kipk !== 'dicabut')
                            <form method="POST" action="{{ route('admin.peringatan.cabut', $item->mahasiswa) }}" class="d-flex flex-wrap align-items-center gap-2" data-confirm="Konfirmasi pencabutan status KIP-K mahasiswa ini?">
                                @csrf
                                <label class="form-check-label"><input class="form-check-input" type="checkbox" name="konfirmasi" value="1" required> Konfirmasi</label>
                                <button class="btn btn-sm btn-danger">Cabut KIP-K</button>
                            </form>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table></div></div>
        <div class="responsive-table-mobile">
            @foreach ($daftar as $item)
                <article class="data-card">
                    <div class="data-card-heading"><strong>{{ $item->mahasiswa->nama }}</strong>
                        @if ($item->tingkat >= $batasPencabutan)<x-status-badge status="pencabutan" />@else<x-status-badge status="evaluasi" />@endif
                    </div>
                    <dl class="row small">
                        <dt class="col-5">NIM</dt><dd class="col-7">{{ $item->mahasiswa->nim }}</dd>
                        <dt class="col-5">Semester</dt><dd class="col-7">{{ $item->semester->semester_ke }}</dd>
                        <dt class="col-5">Tanggal</dt><dd class="col-7">{{ $item->tanggal->format('d-m-Y') }}</dd>
                        <dt class="col-5">Status KIP-K</dt><dd class="col-7"><x-status-badge :status="$item->mahasiswa->status_kipk" /></dd>
                    </dl>
                    @if ($item->tingkat >= $batasPencabutan && $item->mahasiswa->status_kipk !== 'dicabut')
                        <form method="POST" action="{{ route('admin.peringatan.cabut', $item->mahasiswa) }}" class="d-grid gap-2" data-confirm="Konfirmasi pencabutan status KIP-K mahasiswa ini?">
                            @csrf
                            <label class="form-check-label"><input class="form-check-input" type="checkbox" name="konfirmasi" value="1" required> Konfirmasi</label>
                            <button class="btn btn-danger">Cabut KIP-K</button>
                        </form>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@empty
    <div class="data-card empty-state"><h3>Belum ada peringatan IPK</h3><p class="text-secondary mb-0">Peringatan akan tampil setelah nilai semester dievaluasi.</p></div>
@endforelse
<div>{{ $peringatan->links() }}</div>
@endsection
