@extends('layouts.app')
@section('title', 'Nilai saya')
@section('content')
<h1 class="h3 mb-3">Nilai saya</h1>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6"><article class="card stat-card h-100"><div>
        <span class="small-label">IPK terakhir</span><strong class="stat-value">{{ $ipkTerakhir ?? '—' }}</strong>
        <span class="small text-secondary">Nilai kumulatif terbaru</span>
    </div><span class="stat-icon" aria-hidden="true">#</span></article></div>
    <div class="col-12 col-md-6"><article class="card stat-card h-100"><div>
        <span class="small-label">Batas IPK yang berlaku</span><strong class="stat-value">{{ $batas ?? '—' }}</strong>
        <span class="small text-secondary" role="status">{{ $posisi }}</span>
    </div><span class="stat-icon" aria-hidden="true">!</span></article></div>
</div>
<section class="card mb-4" aria-labelledby="grafik-ipk-title">
    <div class="card-body">
        <h2 id="grafik-ipk-title" class="h5">Grafik IPK per semester</h2>
        @if ($semester->isNotEmpty())
            <div class="position-relative" style="height: 280px"><canvas id="grafik-ipk" aria-label="Grafik IPK per semester" role="img"></canvas></div>
        @else
            <p class="text-muted mb-0">Belum ada nilai semester untuk ditampilkan.</p>
        @endif
    </div>
</section>
<section aria-labelledby="nilai-semester-title">
    <h2 id="nilai-semester-title" class="h5">Rincian nilai semester</h2>
    <div class="card responsive-table-desktop"><div class="table-responsive"><table class="table table-striped align-middle mb-0">
        <thead><tr><th>Semester</th><th>IPS</th><th>IPK</th><th>SKS</th><th>KHS</th></tr></thead>
        <tbody>
        @forelse ($semester as $item)
            <tr>
                <td>{{ $item->semester_ke }}</td>
                <td>{{ $item->ips }}</td>
                <td>{{ $item->ipk }}</td>
                <td>{{ $item->sks }}</td>
                <td>@if ($item->file_khs)<a href="{{ route('mahasiswa.khs.download', $item) }}">Unduh KHS</a>@else — @endif</td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="empty-state"><h3>Belum ada data nilai</h3><p class="text-secondary mb-0">Nilai semester akan tampil setelah admin memasukkannya.</p></div></td></tr>
        @endforelse
        </tbody>
    </table></div></div>
    <div class="responsive-table-mobile">
        @forelse ($semester as $item)
            <article class="data-card">
                <div class="data-card-heading"><strong>Semester {{ $item->semester_ke }}</strong><x-status-badge status="tersedia" label="Nilai tersedia" /></div>
                <dl class="row small">
                    <dt class="col-5">IPS</dt><dd class="col-7">{{ $item->ips }}</dd>
                    <dt class="col-5">IPK</dt><dd class="col-7">{{ $item->ipk }}</dd>
                    <dt class="col-5">SKS</dt><dd class="col-7">{{ $item->sks }}</dd>
                    <dt class="col-5">KHS</dt><dd class="col-7 mb-0">@if ($item->file_khs)<a href="{{ route('mahasiswa.khs.download', $item) }}">Unduh KHS</a>@else Belum tersedia @endif</dd>
                </dl>
            </article>
        @empty
            <div class="data-card empty-state"><h3>Belum ada data nilai</h3><p class="text-secondary mb-0">Nilai semester akan tampil setelah admin memasukkannya.</p></div>
        @endforelse
    </div>
</section>
@if ($semester->isNotEmpty())
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        const semesterLabel = {{ Illuminate\Support\Js::from($semester->pluck('semester_ke')->values()) }};
        const nilaiIpk = {{ Illuminate\Support\Js::from($semester->pluck('ipk')->values()) }};
        new Chart(document.getElementById('grafik-ipk'), {
            type: 'line',
            data: {
                labels: semesterLabel.map((nomor) => `Semester ${nomor}`),
                datasets: [{
                    label: 'IPK',
                    data: nilaiIpk.map(Number),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, .15)',
                    tension: .25,
                    fill: true,
                }],
            },
            options: {
                maintainAspectRatio: false,
                scales: { y: { min: 0, max: 4 } },
            },
        });
    </script>
@endif
@endsection
