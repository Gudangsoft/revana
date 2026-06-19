<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Penulis — SIPERA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --grad: linear-gradient(135deg, #1a237e 0%, #283593 55%, #1565c0 100%);
            --grad-soft: linear-gradient(135deg, #e8eaf6 0%, #e3f2fd 100%);
        }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            background: var(--grad);
            font-family: 'Segoe UI', system-ui, sans-serif;
            padding: 28px 16px 48px;
        }
        .portal-wrap { max-width: 720px; margin: 0 auto; }

        /* ── Search card ── */
        .search-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,.22);
        }
        .search-header {
            background: var(--grad);
            color: #fff;
            padding: 28px 32px 24px;
        }
        .search-header .title  { font-size: 1.6rem; font-weight: 800; }
        .search-header .sub    { font-size: .88rem; opacity: .8; margin-top: 4px; }
        .search-body { padding: 28px 32px; }

        .kode-input {
            font-family: monospace;
            font-size: 1.05rem;
            letter-spacing: 1px;
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            padding: 14px 18px;
            transition: border-color .2s;
        }
        .kode-input:focus { border-color: #1a237e; box-shadow: 0 0 0 3px rgba(26,35,126,.1); }

        .btn-cek {
            background: var(--grad);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            font-weight: 700;
            font-size: .95rem;
            transition: opacity .2s, transform .15s;
            white-space: nowrap;
        }
        .btn-cek:hover { opacity: .9; transform: translateY(-1px); color: #fff; }

        /* ── Result card ── */
        .result-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,.22);
            margin-top: 20px;
        }
        .result-header {
            padding: 20px 28px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .result-header .journal-logo {
            width: 52px; height: 52px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255,255,255,.4);
            flex-shrink: 0;
        }
        .result-header .journal-abbr {
            width: 52px; height: 52px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: 14px;
            background: rgba(255,255,255,.2);
            border: 3px solid rgba(255,255,255,.4);
            flex-shrink: 0;
        }
        .result-header .title   { font-size: 1.1rem; font-weight: 700; line-height: 1.3; }
        .result-header .meta    { font-size: .78rem; opacity: .8; margin-top: 3px; }

        .result-body { padding: 24px 28px; }

        /* ── Info rows ── */
        .info-row {
            display: flex;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: .88rem;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label {
            width: 130px;
            flex-shrink: 0;
            color: #6b7280;
            font-weight: 600;
        }
        .info-value { color: #111827; }

        /* ── Progress stepper ── */
        .stepper { display: flex; gap: 0; margin: 20px 0; position: relative; }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step::before {
            content: '';
            position: absolute;
            top: 15px;
            left: calc(-50% + 15px);
            right: calc(50% + 15px);
            height: 3px;
            background: #e5e7eb;
            z-index: 0;
        }
        .step:first-child::before { display: none; }
        .step.done::before  { background: #16a34a; }
        .step.active::before { background: linear-gradient(to right, #16a34a, #3b82f6); }

        .step-dot {
            width: 30px; height: 30px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; font-weight: 700;
            margin: 0 auto 6px;
            position: relative; z-index: 1;
            transition: all .2s;
        }
        .step-label { font-size: .68rem; color: #6b7280; line-height: 1.2; }

        .step.done  .step-dot { background: #16a34a; color: #fff; }
        .step.active .step-dot { background: #3b82f6; color: #fff; box-shadow: 0 0 0 4px rgba(59,130,246,.2); }
        .step.pending .step-dot { background: #f3f4f6; color: #9ca3af; border: 2px solid #e5e7eb; }
        .step.rejected .step-dot { background: #ef4444; color: #fff; }

        /* ── LOA section ── */
        .loa-section {
            background: #f0fdf4;
            border: 2px solid #86efac;
            border-radius: 14px;
            padding: 20px 24px;
            margin-top: 20px;
        }
        .loa-section.locked {
            background: #f9fafb;
            border-color: #e5e7eb;
        }
        .loa-section .loa-title {
            font-weight: 700;
            font-size: .95rem;
            color: #15803d;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }
        .loa-section.locked .loa-title { color: #9ca3af; }

        .btn-loa {
            background: #16a34a;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 11px 24px;
            font-weight: 700;
            font-size: .9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: opacity .2s;
        }
        .btn-loa:hover { opacity: .88; color: #fff; }

        /* ── Footer links ── */
        .portal-footer {
            text-align: center;
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .portal-footer a {
            color: rgba(255,255,255,.75);
            font-size: .82rem;
            text-decoration: none;
            transition: color .15s;
        }
        .portal-footer a:hover { color: #fff; }
    </style>
</head>
<body>
<div class="portal-wrap">

    {{-- ── Search card ─────────────────────────────────────────────── --}}
    <div class="search-card">
        <div class="search-header">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-file-earmark-person-fill" style="font-size:2rem;opacity:.9;"></i>
                <div>
                    <div class="title">Portal Penulis</div>
                    <div class="sub">Cek status artikel & unduh Letter of Acceptance (LOA)</div>
                </div>
            </div>
        </div>
        <div class="search-body">

            @if($errors->any())
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-4">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <form action="{{ route('author.portal.search') }}" method="POST" id="searchForm">
                @csrf
                <label class="form-label fw-semibold mb-2" style="font-size:.9rem;">
                    <i class="bi bi-upc-scan me-1"></i>Kode Submission SIPERA
                </label>
                <div class="d-flex gap-2">
                    <input type="text"
                           name="kode"
                           id="kodeInput"
                           class="form-control kode-input flex-grow-1"
                           placeholder="Contoh: SUB2026060001"
                           value="{{ old('kode', isset($submission) ? $submission->kode_submit : '') }}"
                           autocomplete="off"
                           autofocus>
                    <button type="submit" class="btn btn-cek" id="btnCek">
                        <i class="bi bi-search me-1"></i>Cek
                    </button>
                </div>
                <div class="mt-2" style="font-size:.78rem;color:#9ca3af;">
                    Masukkan kode SIPERA dari email konfirmasi Anda (contoh: SUB2026060001 atau JAF2026060001)
                </div>
            </form>
        </div>
    </div>

    {{-- ── Result ───────────────────────────────────────────────────── --}}
    @isset($submission)
    @php
        $journal  = $submission->journalSlot?->journalMaster;
        $slot     = $submission->journalSlot;
        $realStatus = $submission->getRealStatus();
        $primary  = $journal?->primary_color  ?? '#1A237E';
        $secondary= $journal?->secondary_color ?? '#8B6914';

        // Tentukan stage (1–5)
        $stageMap = [
            'SUBMITTED'         => 1,
            'EDITOR1_PROCESS'   => 2,
            'AUTHOR1_PROCESS'   => 2,
            'EDITOR2_PROCESS'   => 2,
            'REVIEWER1_PROCESS' => 2,
            'REVIEWER2_PROCESS' => 2,
            'EDITOR3_PROCESS'   => 2,
            'AUTHOR2_PROCESS'   => 2,
            'PRODUCTION_PROCESS'=> 3,
            'VALIDATOR_PROCESS' => 4,
            'PUBLISHED'         => 5,
            'REJECTED'          => 0,
        ];
        $stage = $stageMap[$realStatus] ?? 1;

        $loaAvailable = $submission->production_valid || $submission->validator_valid || $realStatus === 'PUBLISHED';

        $statusLabel = [
            'SUBMITTED'          => 'Baru Submit',
            'EDITOR1_PROCESS'    => 'Proses Editor',
            'AUTHOR1_PROCESS'    => 'Proses Author',
            'EDITOR2_PROCESS'    => 'Proses Editor',
            'REVIEWER1_PROCESS'  => 'Proses Review',
            'REVIEWER2_PROCESS'  => 'Proses Review',
            'EDITOR3_PROCESS'    => 'Proses Editor',
            'AUTHOR2_PROCESS'    => 'Proses Author',
            'PRODUCTION_PROCESS' => 'Produksi',
            'VALIDATOR_PROCESS'  => 'Validasi Akhir',
            'PUBLISHED'          => 'Terbit',
            'REJECTED'           => 'Ditolak',
        ][$realStatus] ?? $realStatus;
    @endphp

    <div class="result-card">
        {{-- Header jurnal --}}
        <div class="result-header" style="background:{{ $primary }};color:#fff;">
            @if($journal?->logo_path)
                <img src="{{ Storage::url($journal->logo_path) }}" class="journal-logo" alt="Logo">
            @else
                <div class="journal-abbr" style="color:#fff;">
                    {{ strtoupper(substr($journal?->kode_singkat ?: ($journal?->nama_jurnal ?? 'S'), 0, 2)) }}
                </div>
            @endif
            <div>
                <div class="title">{{ $journal?->nama_jurnal ?? 'Jurnal SIPERA' }}</div>
                <div class="meta">
                    @if($journal?->kode_singkat) {{ $journal->kode_singkat }} · @endif
                    @if($slot) Vol. {{ $slot->volume }}, No. {{ $slot->nomor }}, {{ $slot->bulan }} {{ $slot->tahun }} @endif
                    @if($journal?->e_issn) · E-ISSN {{ $journal->e_issn }} @endif
                </div>
            </div>
            <div class="ms-auto">
                @if($realStatus === 'REJECTED')
                    <span class="badge" style="background:#ef4444;font-size:.78rem;">Ditolak</span>
                @elseif($realStatus === 'PUBLISHED')
                    <span class="badge" style="background:#16a34a;font-size:.78rem;">
                        <i class="bi bi-check-circle-fill me-1"></i>Terbit
                    </span>
                @elseif($loaAvailable)
                    <span class="badge" style="background:#16a34a;font-size:.78rem;">
                        <i class="bi bi-check-circle me-1"></i>Diterima
                    </span>
                @else
                    <span class="badge" style="background:{{ $secondary }};font-size:.78rem;">
                        <i class="bi bi-clock me-1"></i>{{ $statusLabel }}
                    </span>
                @endif
            </div>
        </div>

        <div class="result-body">

            {{-- Info artikel --}}
            <div class="mb-3">
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-file-text me-1"></i>Judul</div>
                    <div class="info-value fw-semibold">{{ $submission->judul_artikel }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-person me-1"></i>Penulis</div>
                    <div class="info-value">{{ $submission->nama_penulis }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-upc me-1"></i>Kode Submit</div>
                    <div class="info-value">
                        <code style="background:#f3f4f6;padding:2px 8px;border-radius:4px;font-size:.88rem;">
                            {{ $submission->kode_submit }}
                        </code>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-calendar3 me-1"></i>Tgl Submit</div>
                    <div class="info-value">
                        {{ $submission->tanggal_submit ? \Carbon\Carbon::parse($submission->tanggal_submit)->isoFormat('D MMMM YYYY') : '—' }}
                    </div>
                </div>
                @if($submission->link_publish)
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-link-45deg me-1"></i>Link Terbit</div>
                    <div class="info-value">
                        <a href="{{ $submission->link_publish }}" target="_blank" class="text-decoration-none" style="color:#1a237e;">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Lihat Artikel
                        </a>
                    </div>
                </div>
                @endif
            </div>

            {{-- Progress stepper --}}
            @if($realStatus !== 'REJECTED')
            <div class="mb-1" style="font-size:.78rem;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">
                Progress Artikel
            </div>
            <div class="stepper">
                @php
                    $steps = [
                        ['label' => 'Submit',      'icon' => 'bi-upload'],
                        ['label' => 'Review',       'icon' => 'bi-eye'],
                        ['label' => 'Produksi',     'icon' => 'bi-printer'],
                        ['label' => 'Validasi',     'icon' => 'bi-shield-check'],
                        ['label' => 'Terbit',       'icon' => 'bi-book'],
                    ];
                @endphp
                @foreach($steps as $idx => $s)
                @php
                    $n = $idx + 1;
                    if ($n < $stage)       $cls = 'done';
                    elseif ($n == $stage)  $cls = 'active';
                    else                   $cls = 'pending';
                @endphp
                <div class="step {{ $cls }}">
                    <div class="step-dot">
                        @if($cls === 'done')
                            <i class="bi bi-check-lg"></i>
                        @else
                            {{ $n }}
                        @endif
                    </div>
                    <div class="step-label">{{ $s['label'] }}</div>
                </div>
                @endforeach
            </div>
            @else
            <div class="alert alert-danger py-2" style="font-size:.88rem;">
                <i class="bi bi-x-circle-fill me-2"></i>Maaf, artikel ini tidak dapat dilanjutkan ke tahap berikutnya.
            </div>
            @endif

            {{-- LOA section --}}
            <div class="loa-section {{ $loaAvailable ? '' : 'locked' }}">
                @if($loaAvailable)
                <div class="loa-title">
                    <i class="bi bi-file-earmark-check-fill" style="color:#16a34a;font-size:1.1rem;"></i>
                    Letter of Acceptance (LOA) Tersedia
                </div>
                <p class="mb-3" style="font-size:.85rem;color:#374151;">
                    Artikel Anda telah diterima. Klik tombol di bawah untuk membuka LOA — bisa diprint atau disimpan sebagai PDF.
                </p>
                <div class="d-flex flex-wrap align-items-center gap-3">
                    {{-- Date picker --}}
                    <div>
                        <label class="form-label mb-1" style="font-size:.78rem;color:#6b7280;font-weight:600;">
                            Tanggal LOA <span class="fw-normal">(opsional, default hari ini)</span>
                        </label>
                        <input type="date" id="loaDate" class="form-control form-control-sm"
                               value="{{ now()->format('Y-m-d') }}"
                               style="width:160px;border-radius:8px;font-size:.85rem;">
                    </div>
                    {{-- Button --}}
                    <div class="mt-3">
                        <a id="btnLoa"
                           href="{{ route('loa.public', ['kode_loa' => $submission->kode_loa]) }}?tanggal={{ now()->format('Y-m-d') }}"
                           class="btn-loa" target="_blank">
                            <i class="bi bi-file-earmark-arrow-down"></i> Buka LOA
                        </a>
                    </div>
                </div>
                @else
                <div class="loa-title">
                    <i class="bi bi-lock-fill" style="color:#d1d5db;"></i>
                    LOA Belum Tersedia
                </div>
                <p class="mb-0" style="font-size:.85rem;color:#9ca3af;">
                    LOA akan tersedia setelah artikel melewati tahap Produksi.
                    Saat ini artikel Anda sedang dalam <strong>{{ $statusLabel }}</strong>.
                </p>
                @endif
            </div>

        </div>
    </div>
    @endisset

    {{-- ── Footer links ───────────────────────────────────────────── --}}
    <div class="portal-footer">
        <a href="{{ route('tracking.index') }}"><i class="bi bi-search me-1"></i>Tracking LOA lama</a>
        <a href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-1"></i>Login Admin</a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    // Uppercase kode
    var kodeInp = document.getElementById('kodeInput');
    if (kodeInp) {
        kodeInp.addEventListener('input', function () {
            var pos = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(pos, pos);
        });
    }

    // LOA date picker → update href
    var datePick = document.getElementById('loaDate');
    var btnLoa   = document.getElementById('btnLoa');
    if (datePick && btnLoa) {
        var baseUrl = btnLoa.href.split('?')[0];
        datePick.addEventListener('change', function () {
            btnLoa.href = baseUrl + (this.value ? '?tanggal=' + this.value : '');
        });
    }

    // Loading state on form submit
    var form = document.getElementById('searchForm');
    var btn  = document.getElementById('btnCek');
    if (form && btn) {
        form.addEventListener('submit', function () {
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mencari…';
            btn.disabled = true;
        });
    }
})();
</script>
</body>
</html>
