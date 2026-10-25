@extends('layouts.app')
@section('title', 'Tambah penerima')
@section('content')
<h1 class="h3 mb-3">Tambah penerima</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.penerima.store') }}" data-confirm="Simpan data penerima baru?">@include('admin.penerima._form')</form></div></div>
@endsection
