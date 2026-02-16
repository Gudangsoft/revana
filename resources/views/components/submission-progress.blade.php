{{-- 
    Submission Progress Bar Component
    Usage: <x-submission-progress :submission="$submission" />
    Optional: <x-submission-progress :submission="$submission" height="10" show-text="true" />
--}}
@props(['submission', 'height' => 8, 'showText' => true, 'minWidth' => 60])

@php
    $progress = $submission->progress_percentage;
    $badgeClass = $submission->status_badge_class;
@endphp

<div class="d-flex align-items-center gap-2">
    <div class="progress flex-grow-1" style="height: {{ $height }}px; min-width: {{ $minWidth }}px;">
        <div class="progress-bar {{ $badgeClass }}" role="progressbar" 
             style="width: {{ $progress }}%" title="{{ $progress }}%"></div>
    </div>
    @if($showText)
        <small class="text-muted" style="min-width: 35px;">{{ $progress }}%</small>
    @endif
</div>
