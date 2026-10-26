@extends('layouts.app')
@section('title', 'Beranda mahasiswa')
@section('content')
<h1 class="h3 mb-3">Halo, {{ $mahasiswa->nama }}</h1>

@if ($peringatan)
    @php($peringatanMelewatiAmbang = $peringatan->tingkat >= $batasPencabutan)
    <div class="banner-sample {{ $peringatanMelewatiAmbang ? 'banner-important' : 'banner-warning' }} mb-3" role="status">
        <span class="banner-symbol" aria-hidden="true">!</span>
        <div><strong>{{ $peringatanMelewatiAmbang ? 'Memenuhi syarat pencabutan' : 'Peringatan evaluasi akademik' }}</strong><p class="mb-0">Peringatan tingkat {{ $peringatan->tingkat }} pada semester {{ $peringatan->semester->semester_ke }}.</p></div>
    </div>
@else
    <div class="banner-sample banner-success mb-3" role="status"><span class="banner-symbol" aria-hidden="true">+</span><div><strong>Status akademik aman</strong><p class="mb-0">Tidak ada peringatan IPK saat ini.</p></div></div>
@endif

<section class="row g-3 mb-4" aria-label="Ringkasan akademik dan status">
    <div class="col-12 col-md-6 col-xl-4">
        <article class="card stat-card h-100"><div>
            <span class="small-label">IPK terakhir</span>
            <strong class="stat-value">{{ $semesterTerakhir?->ipk ?? '—' }}</strong>
            @unless ($semesterTerakhir)<span class="small text-secondary">Belum tersedia</span>@endunless
            <p class="mb-0">Batas IPK saat ini: {{ $aturan?->ipk_minimal ?? 'Belum tersedia' }}</p>
        </div><span class="stat-icon" aria-hidden="true">#</span></article>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <article class="card stat-card h-100"><div>
            <span class="small-label">Status KIP-K</span>
            <strong class="stat-value"><x-status-badge :status="$mahasiswa->status_kipk" /></strong>
            <span class="small text-secondary">Status penerima saat ini</span>
        </div><span class="stat-icon" aria-hidden="true">✓</span></article>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <article class="card stat-card h-100"><div>
            <span class="small-label">Kehadiran pembinaan</span>
            @if ($persentaseKehadiran === null)
                <strong class="stat-value">—</strong><span class="small text-secondary">Belum ada kegiatan selesai</span>
            @else
                <strong class="stat-value">{{ number_format($persentaseKehadiran, 1) }}%</strong>
                <span class="small text-secondary">{{ $jumlahHadir }} dari {{ $jumlahKegiatanSelesai }} kegiatan selesai</span>
                @if ($persentaseKehadiran < config('dashboard.ambang_kehadiran_rendah'))
                    <span class="small text-danger fw-semibold">Kehadiran di bawah {{ config('dashboard.ambang_kehadiran_rendah') }}%.</span>
                @else
                    <span class="small text-success fw-semibold">Kehadiran memenuhi {{ config('dashboard.ambang_kehadiran_rendah') }}%.</span>
                @endif
            @endif
        </div><span class="stat-icon" aria-hidden="true">%</span></article>
    </div>
</section>

@endsection
