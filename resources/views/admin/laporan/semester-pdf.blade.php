<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan nilai semester {{ $semester }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1, h2, p { margin: 0 0 8px; }
        h1 { font-size: 16px; }
        h2 { font-size: 13px; }
        .meta { margin: 14px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #777; padding: 6px; text-align: left; }
        th { background: #eaf0f8; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        .empty { text-align: center; padding: 14px; }
    </style>
</head>
<body>
    <h1>{{ $namaInstansi }}</h1>
    <h2>Laporan nilai akademik semester {{ $semester }}</h2>
    <p class="meta">Tanggal cetak: {{ $tanggalCetak }}</p>
    @if (!empty($filter['prodi']) || !empty($filter['angkatan']))
        <p class="meta">Filter: {{ $filter['prodi'] ?? 'Semua program studi' }} · Angkatan {{ $filter['angkatan'] ?? 'Semua' }}</p>
    @endif
    <table>
        <thead><tr><th>No.</th><th>NIM</th><th>Nama</th><th>Program Studi</th><th>Angkatan</th><th>IPS</th><th>IPK</th><th>SKS</th></tr></thead>
        <tbody>
            @forelse ($data as $baris)
                <tr><td>{{ $loop->iteration }}</td><td>{{ $baris->nim }}</td><td>{{ $baris->nama }}</td><td>{{ $baris->prodi }}</td><td>{{ $baris->angkatan }}</td><td>{{ number_format((float) $baris->ips, 2) }}</td><td>{{ number_format((float) $baris->ipk, 2) }}</td><td>{{ $baris->sks }}</td></tr>
            @empty
                <tr><td class="empty" colspan="8">Tidak ada nilai untuk semester dan filter ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
