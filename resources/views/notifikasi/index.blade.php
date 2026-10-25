@extends('layouts.app')
@section('title', 'Notifikasi')
@section('content')
<h1 class="h3 mb-3">Notifikasi</h1>
<div class="d-grid gap-3">
    @forelse ($notifikasi as $item)
        <article class="card notification-card {{ $item->sudah_dibaca ? '' : 'is-unread' }}">
            <div class="card-body d-flex flex-column flex-sm-row justify-content-between gap-3">
                <div>
                    <p class="mb-1">{{ $item->isi }}</p>
                    <time class="small text-muted" datetime="{{ $item->created_at->toIso8601String() }}">{{ $item->created_at->format('d-m-Y H:i') }}</time>
                    <x-status-badge :status="$item->sudah_dibaca ? 'sudah_dibaca' : 'belum_dibaca'" :label="$item->sudah_dibaca ? 'Sudah dibaca' : 'Belum dibaca'" class="ms-2" />
                </div>
                @unless ($item->sudah_dibaca)
                    <form method="POST" action="{{ route('notifikasi.baca', $item) }}" class="align-self-start">
                        @csrf
                        <button class="btn btn-sm btn-outline-primary">Tandai sudah dibaca</button>
                    </form>
                @endunless
            </div>
        </article>
    @empty
        <div class="data-card empty-state" role="status"><h3>Belum ada notifikasi</h3><p class="text-secondary mb-0">Notifikasi baru akan tampil di sini.</p></div>
    @endforelse
</div>
<div class="mt-3">{{ $notifikasi->links() }}</div>
@endsection
