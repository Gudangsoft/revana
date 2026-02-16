{{-- 
    Submission Progress Bar Component
    Usage: <x-submission-progress :submission="$submission" />
    Optional: <x-submission-progress :submission="$submission" height="10" show-text="true" />
--}}
@props(['submission', 'height' => null, 'showText' => null, 'minWidth' => 60])

@php
    use App\Services\ComponentSettingService;
    $settings = ComponentSettingService::all();
    $barHeight = $height ?? ($settings['progress_height'] ?? 8);
    $displayText = $showText ?? ($settings['progress_show_text'] ?? true);
    $progress = $submission->progress_percentage;
    $badgeClass = $submission->status_badge_class;
@endphp

<div class="d-flex align-items-center gap-2">
    <div class="progress flex-grow-1" style="height: {{ $barHeight }}px; min-width: {{ $minWidth }}px;">
        <div class="progress-bar {{ $badgeClass }}" role="progressbar" 
             style="width: {{ $progress }}%" title="{{ $progress }}%"></div>
    </div>
    @if($displayText)
        <small class="text-muted" style="min-width: 35px;">{{ $progress }}%</small>
    @endif
</div>
