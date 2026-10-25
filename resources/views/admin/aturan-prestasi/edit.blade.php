@extends('layouts.app')
@section('title', 'Ubah Aturan Prestasi')
@section('content')
<h1 class="h3 mb-3">Ubah Aturan Prestasi</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.aturan-prestasi.update', $aturanPrestasi) }}">@method('PUT') @include('admin.aturan-prestasi._form')</form></div></div>
<form method="POST" action="{{ route('admin.aturan-prestasi.destroy', $aturanPrestasi) }}" class="mt-3" onsubmit="return confirm('Hapus aturan mendatang yang belum berlaku?')">@csrf @method('DELETE')<button class="btn btn-outline-danger">Hapus aturan mendatang</button></form>
@endsection
