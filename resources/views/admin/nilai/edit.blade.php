@extends('layouts.app')
@section('title', 'Ubah Nilai Semester')
@section('content')
<h1 class="h3 mb-1">Ubah Nilai Semester</h1>
<p class="text-muted mb-3">{{ $penerima->nama }} ({{ $penerima->nim }})</p>
<div class="card"><div class="card-body"><form method="POST" enctype="multipart/form-data" action="{{ route('admin.penerima.nilai.update', [$penerima, $semester]) }}">@method('PUT') @include('admin.nilai._form')</form></div></div>
@endsection
