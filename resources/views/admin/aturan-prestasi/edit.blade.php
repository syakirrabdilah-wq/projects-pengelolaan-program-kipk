@extends('layouts.app')
@section('title', 'Ubah aturan prestasi')
@section('content')
<h1 class="h3 mb-3">Ubah aturan prestasi</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.aturan-prestasi.update', $aturanPrestasi) }}" data-confirm="Simpan perubahan aturan prestasi?">@method('PUT') @include('admin.aturan-prestasi._form')</form></div></div>
<form method="POST" action="{{ route('admin.aturan-prestasi.destroy', $aturanPrestasi) }}" class="mt-3" data-confirm="Hapus aturan mendatang yang belum berlaku?">@csrf @method('DELETE')<button class="btn btn-outline-danger">Hapus aturan mendatang</button></form>
@endsection
