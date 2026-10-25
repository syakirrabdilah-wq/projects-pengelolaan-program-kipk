@extends('layouts.app')
@section('title', 'Rekap kehadiran')
@section('content')
<h1 class="h3 mb-3">Rekap kehadiran</h1>
<p>{{ $mahasiswa->nama }} · {{ $mahasiswa->nim }}</p>
<div class="banner-sample banner-info mb-3" role="status"><span class="banner-symbol" aria-hidden="true">i</span><div><strong>Kehadiran {{ number_format($persentase, 1) }}%</strong><p class="mb-0">Dari {{ $jumlahSelesai }} kegiatan yang sudah selesai.</p></div></div>
<div class="card responsive-table-desktop"><div class="table-responsive"><table class="table table-striped mb-0">
    <thead><tr><th>Kegiatan</th><th>Tanggal</th><th>Status</th><th>Foto dan waktu diterima</th></tr></thead>
    <tbody>
    @forelse ($kegiatan as $item)
        @php($catatan = $item->absensi->first())
        <tr>
            <td>{{ $item->judul }}</td><td>{{ $item->tanggal->format('d-m-Y') }}</td>
            <td><x-status-badge :status="$catatan?->status ?? ($item->absen_tutup->lte(now()) ? 'tidak_hadir' : 'belum_berlangsung')" /></td>
            <td>
                @if ($catatan?->file_foto)
                    <a href="{{ route('admin.absensi.foto', $catatan) }}" target="_blank" rel="noopener">Lihat foto</a>
                    <div class="small text-muted">{{ $catatan->waktu->timezone(config('app.timezone'))->format('d-m-Y H:i:s') }} ({{ config('app.timezone') }})</div>
                @else
                    <span class="text-muted">Belum ada foto</span>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="4"><div class="empty-state"><h3>Belum ada kegiatan</h3><p class="text-secondary mb-0">Riwayat kehadiran akan tampil setelah jadwal pembinaan dibuat.</p></div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="responsive-table-mobile">
    @forelse ($kegiatan as $item)
        @php($catatan = $item->absensi->first())
        <article class="data-card">
            <div class="data-card-heading"><strong>{{ $item->judul }}</strong><x-status-badge :status="$catatan?->status ?? ($item->absen_tutup->lte(now()) ? 'tidak_hadir' : 'belum_berlangsung')" /></div>
            <dl class="row small">
                <dt class="col-5">Tanggal</dt><dd class="col-7">{{ $item->tanggal->format('d-m-Y') }}</dd>
                <dt class="col-5">Bukti foto</dt><dd class="col-7 mb-0">
                    @if ($catatan?->file_foto)
                        <a href="{{ route('admin.absensi.foto', $catatan) }}" target="_blank" rel="noopener">Lihat foto</a>
                        <div class="text-muted">{{ $catatan->waktu->timezone(config('app.timezone'))->format('d-m-Y H:i:s') }} ({{ config('app.timezone') }})</div>
                    @else
                        Belum tersedia
                    @endif
                </dd>
            </dl>
        </article>
    @empty
        <div class="data-card empty-state"><h3>Belum ada kegiatan</h3><p class="text-secondary mb-0">Riwayat kehadiran akan tampil setelah jadwal pembinaan dibuat.</p></div>
    @endforelse
</div>
@endsection
