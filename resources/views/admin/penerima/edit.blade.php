@extends('layouts.app')
@section('title', 'Ubah penerima')
@section('content')
<h1 class="h3 mb-3">Ubah penerima</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.penerima.update', $penerima) }}" data-confirm="Simpan perubahan data penerima?">@method('PUT') @include('admin.penerima._form')</form></div></div>
@endsection
