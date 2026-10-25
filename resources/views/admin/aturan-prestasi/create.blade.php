@extends('layouts.app')
@section('title', 'Tambah Aturan Prestasi')
@section('content')
<h1 class="h3 mb-3">Tambah Aturan Prestasi</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.aturan-prestasi.store') }}">@include('admin.aturan-prestasi._form')</form></div></div>
@endsection
