{{--
  Sync Point Reminder Banner
  Parameters:
    $syncRoute   - route for sync action (string)
    $syncLabel   - button label (string)
    $syncMethod  - 'GET' or 'POST' (string, default 'GET')
    $reminderId  - unique localStorage key (string)
--}}
@php
    $reminderId  = $reminderId  ?? 'sync_point_reminder';
    $syncMethod  = strtoupper($syncMethod ?? 'GET');
    $syncLabel   = $syncLabel   ?? 'Sinkronkan Sekarang';
@endphp

<div id="{{ $reminderId }}_banner" class="alert alert-warning border-warning shadow-sm mb-3" role="alert" style="display:none; border-left: 5px solid #ffc107 !important;">
    <div class="d-flex align-items-start gap-3">
        <div class="flex-shrink-0 mt-1">
            <i class="bi bi-arrow-repeat text-warning fs-3"></i>
        </div>
        <div class="flex-grow-1">
            <div class="fw-bold mb-1">
                <i class="bi bi-bell-fill me-1"></i> Pengingat Sinkronisasi Point
            </div>
            <div class="text-muted small mb-2">
                Pastikan data point selalu akurat dan terkini. Lakukan sinkronisasi secara rutin agar riwayat tugas dan total point sesuai dengan data real di sistem.
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if($syncMethod === 'POST')
                <form method="POST" action="{{ $syncRoute }}" class="d-inline" id="{{ $reminderId }}_form">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm fw-semibold" onclick="dismissReminder('{{ $reminderId }}')">
                        <i class="bi bi-arrow-repeat me-1"></i> {{ $syncLabel }}
                    </button>
                </form>
                @else
                <a href="{{ $syncRoute }}" class="btn btn-warning btn-sm fw-semibold" onclick="dismissReminder('{{ $reminderId }}')">
                    <i class="bi bi-arrow-repeat me-1"></i> {{ $syncLabel }}
                </a>
                @endif
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="dismissReminder('{{ $reminderId }}')">
                    <i class="bi bi-x me-1"></i> Ingatkan besok
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var key       = '{{ $reminderId }}_dismissed_at';
    var bannerId  = '{{ $reminderId }}_banner';
    var dismissed = localStorage.getItem(key);
    var now       = Date.now();
    var oneDay    = 24 * 60 * 60 * 1000;

    if (!dismissed || (now - parseInt(dismissed)) > oneDay) {
        var el = document.getElementById(bannerId);
        if (el) el.style.display = 'block';
    }
})();

function dismissReminder(id) {
    localStorage.setItem(id + '_dismissed_at', Date.now().toString());
    var el = document.getElementById(id + '_banner');
    if (el) el.style.display = 'none';
}
</script>
