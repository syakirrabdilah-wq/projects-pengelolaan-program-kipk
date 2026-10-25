@extends('layouts.app')
@section('title', 'Tambah aturan prestasi')
@section('content')
<h1 class="h3 mb-3">Tambah aturan prestasi</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.aturan-prestasi.store') }}" data-confirm="Simpan perubahan aturan prestasi?">@include('admin.aturan-prestasi._form')</form></div></div>
@endsection
