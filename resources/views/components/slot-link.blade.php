{{-- 
    Slot Link Component
    Usage: <x-slot-link :journal-slot="$slot" guard="marketing" />
    Guard options: 'marketing', 'pic', 'admin'
--}}
@props(['journalSlot', 'guard' => 'marketing'])

@php
    $route = match($guard) {
        'marketing' => route('marketing.journal-slots.show', $journalSlot),
        'pic' => route('pic.journal-slots.show', $journalSlot),
        'admin' => route('admin.journal-slots.show', $journalSlot),
        default => '#',
    };
@endphp

<a href="{{ $route }}" class="text-decoration-none">
    <code>{{ $journalSlot->kode_slot }}</code>
</a>
