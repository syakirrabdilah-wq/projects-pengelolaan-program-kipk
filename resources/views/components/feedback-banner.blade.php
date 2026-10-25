@props(['tone' => 'info', 'title' => null])
@php
    $tone = in_array($tone, ['info', 'success', 'warning', 'important'], true) ? $tone : 'info';
    $icon = ['info' => 'i', 'success' => '+', 'warning' => '!', 'important' => '!'][$tone];
    $role = in_array($tone, ['warning', 'important'], true) ? 'alert' : 'status';
@endphp
<div {{ $attributes->class(['banner-sample', "banner-{$tone}"]) }} role="{{ $role }}">
    <span class="banner-symbol" aria-hidden="true">{{ $icon }}</span>
    <div>
        @if ($title)<strong>{{ $title }}</strong>@endif
        <p class="mb-0">{{ $slot }}</p>
    </div>
</div>
