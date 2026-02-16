{{-- 
    Submission Status Badge Component
    Usage: <x-submission-status :submission="$submission" />
    Optional: <x-submission-status :submission="$submission" size="small" />
--}}
@props(['submission', 'size' => 'normal'])

@php
    $sizeClass = $size === 'small' ? 'small' : '';
@endphp

<span class="badge {{ $submission->status_badge_class }} {{ $sizeClass }}">
    {{ $submission->status_label }}
</span>
