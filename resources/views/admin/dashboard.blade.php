@extends('layouts.app')
@section('title', 'Dashboard admin')
@section('content')
<h1 class="h3 mb-3">Dashboard admin</h1>
<section class="row g-3 mb-4" aria-label="Ringkasan penerima">
    @foreach ([
        ['Total penerima', $ringkasan['total_penerima'], 'seluruh penerima', '●'],
        ['Penerima aktif', $ringkasan['penerima_aktif'], 'berstatus aktif', '✓'],
        ['Dalam peringatan', $ringkasan['dalam_peringatan'], 'perlu dipantau', '!'],
        ['Memenuhi syarat pencabutan', $ringkasan['memenuhi_syarat_pencabutan'], 'menunggu keputusan admin', '!'],
        ['Belum kirim dokumentasi minggu ini', $ringkasan['belum_kirim_dokumentasi'], 'perlu diingatkan', '•'],
    ] as [$label, $jumlah, $keterangan, $ikon])
        <div class="col-12 col-sm-6 col-xl">
            <article class="card stat-card h-100"><div>
                <span class="small-label">{{ $label }}</span>
                <strong class="stat-value">{{ $jumlah }}</strong>
                <span class="small text-secondary">{{ $keterangan }}</span>
            </div><span class="stat-icon" aria-hidden="true">
                {{ $ikon }}
            </span></article>
        </div>
    @endforeach
</section>

<section class="row g-3 mb-4" aria-label="Grafik penerima">
    <div class="col-12 col-lg-6">
        <article class="card h-100"><div class="card-body">
            <h2 class="h5">Penerima per program studi</h2>
            @if ($grafik['label_prodi']->isNotEmpty())
                <div class="position-relative" style="height: 280px"><canvas id="grafik-prodi" role="img" aria-label="Grafik jumlah penerima per program studi"></canvas></div>
            @else
                <div class="empty-state"><h3>Belum ada data penerima</h3><p class="text-secondary mb-0">Data grafik muncul setelah penerima ditambahkan.</p></div>
            @endif
        </div></article>
    </div>
    <div class="col-12 col-lg-6">
        <article class="card h-100"><div class="card-body">
            <h2 class="h5">Penerima per angkatan</h2>
            @if ($grafik['label_angkatan']->isNotEmpty())
                <div class="position-relative" style="height: 280px"><canvas id="grafik-angkatan" role="img" aria-label="Grafik jumlah penerima per angkatan"></canvas></div>
            @else
                <div class="empty-state"><h3>Belum ada data angkatan</h3><p class="text-secondary mb-0">Grafik akan tampil setelah data penerima tersedia.</p></div>
            @endif
        </div></article>
    </div>
</section>

<section aria-labelledby="mahasiswa-bermasalah-title">
    <h2 id="mahasiswa-bermasalah-title" class="h5">Mahasiswa yang perlu ditinjau</h2>
    <p class="text-muted">Kehadiran rendah berarti kurang dari {{ $ambangKehadiranRendah }}% pada kegiatan yang sudah selesai.</p>
    <div class="card responsive-table-desktop"><div class="table-responsive"><table class="table table-striped align-middle mb-0">
        <thead><tr><th>Mahasiswa</th><th>NIM</th><th>IPK terakhir</th><th>Kehadiran</th><th>Hal yang perlu ditinjau</th></tr></thead>
        <tbody>
        @forelse ($mahasiswaBermasalah as $item)
            <tr>
                <td>{{ $item['nama'] }}</td><td>{{ $item['nim'] }}</td>
                <td>{{ $item['ipk'] === null ? '—' : number_format($item['ipk'], 2) }}</td>
                <td>{{ $item['persentase_kehadiran'] === null ? '—' : number_format($item['persentase_kehadiran'], 1).'%' }}</td>
                <td><div class="d-flex flex-wrap gap-1">@foreach ($item['alasan'] as $alasan)<x-status-badge status="menunggu" :label="$alasan" />@endforeach</div></td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="empty-state"><h3>Belum ada mahasiswa yang perlu ditinjau</h3><p class="text-secondary mb-0">Daftar ini akan menampilkan mahasiswa yang perlu ditindaklanjuti.</p></div></td></tr>
        @endforelse
        </tbody>
    </table></div></div>
    <div class="responsive-table-mobile">
        @forelse ($mahasiswaBermasalah as $item)
            <article class="data-card">
                <div class="data-card-heading"><strong>{{ $item['nama'] }}</strong><x-status-badge status="menunggu" label="Perlu ditinjau" /></div>
                <dl class="row small">
                    <dt class="col-5">NIM</dt><dd class="col-7">{{ $item['nim'] }}</dd>
                    <dt class="col-5">IPK terakhir</dt><dd class="col-7">{{ $item['ipk'] === null ? '—' : number_format($item['ipk'], 2) }}</dd>
                    <dt class="col-5">Kehadiran</dt><dd class="col-7">{{ $item['persentase_kehadiran'] === null ? '—' : number_format($item['persentase_kehadiran'], 1).'%' }}</dd>
                    <dt class="col-5">Perlu ditinjau</dt><dd class="col-7 mb-0"><div class="d-flex flex-wrap gap-1">@foreach ($item['alasan'] as $alasan)<x-status-badge status="menunggu" :label="$alasan" />@endforeach</div></dd>
                </dl>
            </article>
        @empty
            <div class="data-card empty-state"><h3>Belum ada mahasiswa yang perlu ditinjau</h3><p class="text-secondary mb-0">Daftar ini akan menampilkan mahasiswa yang perlu ditindaklanjuti.</p></div>
        @endforelse
    </div>
</section>

@if ($grafik['label_prodi']->isNotEmpty() || $grafik['label_angkatan']->isNotEmpty())
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        @if ($grafik['label_prodi']->isNotEmpty())
            new Chart(document.getElementById('grafik-prodi'), {
                type: 'bar',
                data: {
                    labels: {{ Illuminate\Support\Js::from($grafik['label_prodi']) }},
                    datasets: [{ label: 'Jumlah penerima', data: {{ Illuminate\Support\Js::from($grafik['jumlah_prodi']) }}, backgroundColor: '#15803d' }],
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
            });
        @endif
        @if ($grafik['label_angkatan']->isNotEmpty())
            new Chart(document.getElementById('grafik-angkatan'), {
                type: 'bar',
                data: {
                    labels: {{ Illuminate\Support\Js::from($grafik['label_angkatan']) }},
                    datasets: [{ label: 'Jumlah penerima', data: {{ Illuminate\Support\Js::from($grafik['jumlah_angkatan']) }}, backgroundColor: '#0f766e' }],
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
            });
        @endif
    </script>
@endif
@endsection
