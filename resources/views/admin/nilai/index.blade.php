@extends('layouts.app')
@section('title', 'Nilai akademik')
@section('content')
<div class="page-heading-row d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div><h1 class="h3 mb-1">Nilai akademik</h1><p class="text-muted mb-0">{{ $penerima->nama }} ({{ $penerima->nim }})</p></div>
    <a class="btn btn-primary" href="{{ route('admin.penerima.nilai.create', $penerima) }}">Tambah nilai semester</a>
</div>
<div class="card responsive-table-desktop"><div class="table-responsive"><table class="table table-striped mb-0 align-middle">
    <thead><tr><th>Semester</th><th>IPS</th><th>IPK</th><th>SKS</th><th>Diinput oleh</th><th>KHS</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($semester as $item)
        <tr>
            <td>{{ $item->semester_ke }}</td><td>{{ $item->ips }}</td><td>{{ $item->ipk }}</td><td>{{ $item->sks }}</td>
            <td>{{ $item->diinputOleh->username }}</td>
            <td>@if ($item->file_khs)<a href="{{ route('admin.nilai.khs', $item) }}">Unduh KHS</a>@else — @endif</td>
            <td><a href="{{ route('admin.penerima.nilai.edit', [$penerima, $item]) }}">Ubah</a></td>
        </tr>
    @empty
        <tr><td colspan="7"><div class="empty-state"><h3>Belum ada nilai semester</h3><p class="text-secondary mb-0">Tambahkan nilai semester untuk mulai melihat riwayat akademik.</p></div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="responsive-table-mobile">
    @forelse ($semester as $item)
        <article class="data-card">
            <div class="data-card-heading"><strong>Semester {{ $item->semester_ke }}</strong><span class="small text-secondary">{{ $item->sks }} SKS</span></div>
            <dl class="row small">
                <dt class="col-5">IPS</dt><dd class="col-7">{{ $item->ips }}</dd>
                <dt class="col-5">IPK</dt><dd class="col-7">{{ $item->ipk }}</dd>
                <dt class="col-5">Diinput oleh</dt><dd class="col-7">{{ $item->diinputOleh->username }}</dd>
                <dt class="col-5">KHS</dt><dd class="col-7">@if ($item->file_khs)<a href="{{ route('admin.nilai.khs', $item) }}">Unduh KHS</a>@else Belum tersedia @endif</dd>
            </dl>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.penerima.nilai.edit', [$penerima, $item]) }}">Ubah nilai</a>
        </article>
    @empty
        <div class="data-card empty-state"><h3>Belum ada nilai semester</h3><p class="text-secondary mb-0">Tambahkan nilai semester untuk mulai melihat riwayat akademik.</p></div>
    @endforelse
</div>
<div class="mt-3">{{ $semester->links() }}</div>
<a class="btn btn-outline-secondary mt-2" href="{{ route('admin.penerima.index') }}">Kembali ke penerima</a>
@endsection
