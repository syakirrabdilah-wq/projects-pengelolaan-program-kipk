@extends('layouts.app')
@section('title', 'Nilai Akademik')
@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div><h1 class="h3 mb-1">Nilai Akademik</h1><p class="text-muted mb-0">{{ $penerima->nama }} ({{ $penerima->nim }})</p></div>
    <a class="btn btn-primary" href="{{ route('admin.penerima.nilai.create', $penerima) }}">Tambah nilai semester</a>
</div>
<div class="card"><div class="table-responsive"><table class="table table-striped mb-0 align-middle">
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
        <tr><td colspan="7" class="text-center py-4">Belum ada nilai semester.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $semester->links() }}</div>
<a class="btn btn-outline-secondary mt-2" href="{{ route('admin.penerima.index') }}">Kembali ke penerima</a>
@endsection
