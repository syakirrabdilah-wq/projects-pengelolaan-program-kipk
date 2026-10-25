@extends('layouts.app')
@section('title', 'Aturan prestasi')
@section('content')
<div class="page-heading-row d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Aturan prestasi</h1>
    <a class="btn btn-primary" href="{{ route('admin.aturan-prestasi.create') }}">Tambah aturan</a>
</div>
@if ($errors->has('aturan'))<x-feedback-banner tone="important" title="Aturan belum disimpan" class="mb-3">{{ $errors->first('aturan') }}</x-feedback-banner>@endif
<div class="card responsive-table-desktop"><div class="table-responsive"><table class="table table-striped mb-0 align-middle">
    <thead><tr><th>Berlaku mulai</th><th>Batas IPK minimal</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($aturan as $item)
        <tr><td>{{ $item->berlaku_mulai->format('d-m-Y') }}</td><td>{{ $item->ipk_minimal }}</td><td><a href="{{ route('admin.aturan-prestasi.edit', $item) }}">Ubah</a></td></tr>
    @empty
        <tr><td colspan="3"><div class="empty-state"><h3>Belum ada aturan prestasi</h3><p class="text-secondary mb-0">Tambahkan batas IPK dan tanggal mulai berlakunya.</p></div></td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="responsive-table-mobile">
    @forelse ($aturan as $item)
        <article class="data-card">
            <div class="data-card-heading"><strong>Batas IPK</strong><x-status-badge status="tersedia" :label="number_format($item->ipk_minimal, 2)" /></div>
            <dl class="row small"><dt class="col-5">Berlaku mulai</dt><dd class="col-7">{{ $item->berlaku_mulai->format('d-m-Y') }}</dd></dl>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.aturan-prestasi.edit', $item) }}">Ubah aturan</a>
        </article>
    @empty
        <div class="data-card empty-state"><h3>Belum ada aturan prestasi</h3><p class="text-secondary mb-0">Tambahkan batas IPK dan tanggal mulai berlakunya.</p></div>
    @endforelse
</div>
<div class="mt-3">{{ $aturan->links() }}</div>
@endsection
