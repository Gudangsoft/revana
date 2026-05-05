{{--
    Auto-Refresh Partial
    Parameters:
      $interval  : detik antar refresh (default: 30)
      $arId      : ID unik jika ada beberapa instance (default: 'default')
--}}
@php
    $arInterval = $interval ?? 30;
    $arId       = $arId ?? 'default';
@endphp

<div class="d-flex align-items-center gap-2 mb-3" id="auto-refresh-bar-{{ $arId }}">
    <div class="d-flex align-items-center gap-1 px-2 py-1 rounded border"
         style="background:#f8f9fa; font-size:0.8rem; white-space:nowrap;">
        <i class="bi bi-arrow-repeat text-success" id="ar-icon-{{ $arId }}"></i>
        <span class="text-muted">Auto-refresh:</span>
        <span id="ar-countdown-{{ $arId }}" class="fw-bold text-success" style="min-width:28px;">{{ $arInterval }}s</span>
        <button type="button" id="ar-toggle-{{ $arId }}"
                class="btn btn-sm p-0 ms-1 border-0 lh-1"
                title="Jeda / Lanjutkan auto-refresh">
            <i class="bi bi-pause-circle text-warning fs-6" id="ar-toggle-icon-{{ $arId }}"></i>
        </button>
    </div>
    <small class="text-muted" id="ar-status-{{ $arId }}">
        Halaman diperbarui otomatis setiap {{ $arInterval }} detik
    </small>
</div>

<script>
(function () {
    var ID       = '{{ $arId }}';
    var INTERVAL = {{ $arInterval }};
    var SK       = 'ar_paused_' + ID;

    var cdEl     = document.getElementById('ar-countdown-' + ID);
    var iconEl   = document.getElementById('ar-icon-' + ID);
    var toggleEl = document.getElementById('ar-toggle-' + ID);
    var ticonEl  = document.getElementById('ar-toggle-icon-' + ID);
    var statusEl = document.getElementById('ar-status-' + ID);

    var countdown    = INTERVAL;
    var paused       = sessionStorage.getItem(SK) === '1';
    var interacting  = false;
    var interactTimer = null;

    function setPaused(val) {
        paused = val;
        sessionStorage.setItem(SK, val ? '1' : '0');
        countdown = INTERVAL;
        cdEl.textContent = countdown + 's';
        cdEl.classList.remove('text-danger');
        render();
    }

    function render() {
        if (paused) {
            cdEl.classList.replace('text-success', 'text-muted');
            iconEl.classList.replace('text-success', 'text-muted');
            ticonEl.className = 'bi bi-play-circle text-success fs-6';
            statusEl.textContent = 'Auto-refresh dijeda';
        } else {
            cdEl.classList.remove('text-muted');
            cdEl.classList.add('text-success');
            iconEl.classList.remove('text-muted');
            iconEl.classList.add('text-success');
            ticonEl.className = 'bi bi-pause-circle text-warning fs-6';
            statusEl.textContent = 'Halaman diperbarui otomatis setiap ' + INTERVAL + ' detik';
        }
    }

    render();

    setInterval(function () {
        if (paused || interacting) {
            if (!paused) {
                cdEl.textContent = '—';
                statusEl.textContent = 'Dijeda sementara (sedang input)…';
            }
            return;
        }
        countdown--;
        cdEl.textContent = countdown + 's';
        if (countdown <= 5) {
            cdEl.classList.remove('text-success');
            cdEl.classList.add('text-danger');
        }
        if (countdown <= 0) {
            location.reload();
        }
    }, 1000);

    toggleEl.addEventListener('click', function () {
        setPaused(!paused);
    });

    // Pause while user is typing/selecting
    document.addEventListener('focusin', function (e) {
        var tag = e.target && e.target.tagName;
        if (tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA') {
            interacting = true;
            clearTimeout(interactTimer);
        }
    });

    document.addEventListener('focusout', function (e) {
        var tag = e.target && e.target.tagName;
        if (tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA') {
            clearTimeout(interactTimer);
            // Resume setelah 3 detik agar perubahan select sempat terkirim
            interactTimer = setTimeout(function () {
                interacting = false;
                if (!paused) {
                    countdown = INTERVAL;
                    cdEl.textContent = countdown + 's';
                    cdEl.classList.remove('text-danger');
                    render();
                }
            }, 3000);
        }
    });
})();
</script>
