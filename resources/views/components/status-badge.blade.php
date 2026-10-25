@props(['status', 'label' => null])
@php
    $normalized = str($status)->lower()->replace(' ', '_')->toString();
    $tone = match ($normalized) {
        'aktif', 'hadir', 'diterima', 'tepat_waktu', 'tersedia' => 'success',
        'menunggu', 'evaluasi', 'terlambat', 'belum_berlangsung' => 'warning',
        'alpa', 'ditolak', 'dicabut', 'pencabutan', 'tidak_hadir' => 'danger',
        'belum_dibaca' => 'info',
        default => 'neutral',
    };
    $label ??= str($status)->replace('_', ' ')->ucfirst()->toString();
@endphp
<span {{ $attributes->class(['status-badge', "status-badge--{$tone}"]) }}>{{ $label }}</span>
