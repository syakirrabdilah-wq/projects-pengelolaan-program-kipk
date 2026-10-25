@extends('layouts.app')
@section('title', 'Detail Penerima')
@section('content')
<h1 class="h3 mb-3">Detail penerima</h1>
<div class="card"><dl class="row card-body mb-0">
    <dt class="col-sm-3">NIM</dt><dd class="col-sm-9">{{ $penerima->nim }}</dd>
    <dt class="col-sm-3">Nama</dt><dd class="col-sm-9">{{ $penerima->nama }}</dd>
    <dt class="col-sm-3">Program studi</dt><dd class="col-sm-9">{{ $penerima->prodi }}</dd>
    <dt class="col-sm-3">Angkatan</dt><dd class="col-sm-9">{{ $penerima->angkatan }}</dd>
    <dt class="col-sm-3">Status KIP-K</dt><dd class="col-sm-9"><x-status-badge :status="$penerima->status_kipk" /></dd>
    <dt class="col-sm-3">Akun</dt><dd class="col-sm-9"><x-status-badge :status="$penerima->user->aktif ? 'aktif' : 'nonaktif'" /> ({{ $penerima->user->username }})</dd>
</dl></div>
<div class="d-flex gap-2 mt-3">
    <a class="btn btn-primary" href="{{ route('admin.penerima.edit', $penerima) }}">Ubah</a>
    <form method="POST" action="{{ route('admin.penerima.destroy', $penerima) }}" data-confirm="Hapus penerima ini beserta akun dan data terkait?">@csrf @method('DELETE')<button class="btn btn-outline-danger">Hapus</button></form>
    <a class="btn btn-outline-secondary" href="{{ route('admin.penerima.index') }}">Kembali</a>
</div>
@endsection
