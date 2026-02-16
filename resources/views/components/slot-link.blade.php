{{-- 
    Slot Link Component
    Usage: <x-slot-link :slot="$slot" :guard="'marketing'" />
    Guard options: 'marketing', 'pic', 'admin'
--}}
@props(['slot', 'guard' => 'marketing'])

@php
    $route = match($guard) {
        'marketing' => route('marketing.journal-slots.show', $slot),
        'pic' => route('pic.journal-slots.show', $slot),
        'admin' => route('admin.journal-slots.show', $slot),
        default => '#',
    };
@endphp

<a href="{{ $route }}" class="text-decoration-none">
    <code>{{ $slot->kode_slot }}</code>
</a>
