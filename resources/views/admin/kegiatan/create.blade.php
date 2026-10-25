@extends('layouts.app')
@section('title', 'Tambah kegiatan pembinaan')
@section('content')
<h1 class="h3 mb-3">Tambah kegiatan pembinaan</h1>
@include('admin.kegiatan._form', ['aksi' => route('admin.kegiatan.store'), 'method' => 'POST'])
@endsection
