@extends('layouts.app')
@section('title', 'Aturan Prestasi')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Aturan Prestasi</h1>
    <a class="btn btn-primary" href="{{ route('admin.aturan-prestasi.create') }}">Tambah aturan</a>
</div>
@if ($errors->has('aturan'))<div class="alert alert-danger">{{ $errors->first('aturan') }}</div>@endif
<div class="card"><div class="table-responsive"><table class="table table-striped mb-0 align-middle">
    <thead><tr><th>Berlaku mulai</th><th>Batas IPK minimal</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($aturan as $item)
        <tr><td>{{ $item->berlaku_mulai->format('d-m-Y') }}</td><td>{{ $item->ipk_minimal }}</td><td><a href="{{ route('admin.aturan-prestasi.edit', $item) }}">Ubah</a></td></tr>
    @empty
        <tr><td colspan="3" class="text-center py-4">Belum ada aturan prestasi.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $aturan->links() }}</div>
@endsection
