@extends('layouts.app')
@section('title', 'Ubah kegiatan pembinaan')
@section('content')
<h1 class="h3 mb-3">Ubah kegiatan pembinaan</h1>
@include('admin.kegiatan._form', ['aksi' => route('admin.kegiatan.update', $kegiatan), 'method' => 'PUT'])
@endsection
