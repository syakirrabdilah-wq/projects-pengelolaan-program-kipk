@extends('layouts.app')
@section('title', 'Tambah Penerima')
@section('content')
<h1 class="h3 mb-3">Tambah Penerima</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.penerima.store') }}">@include('admin.penerima._form')</form></div></div>
@endsection
