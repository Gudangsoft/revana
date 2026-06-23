@extends('layouts.app')

@section('title', 'Master LOA')
@section('page-title', 'Master LOA')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row g-3">

    @if(session('success'))
    <div class="col-12">
        <div class="alert alert-success alert-dismissible fade show mb-0">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    {{-- ── Stat cards ────────────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100" style="cursor:pointer;" onclick="setFilter('all','all')">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:42px;height:42px;border-radius:10px;background:#eef2ff;
                                        display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-journals" style="color:#4f46e5;font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <div style="font-size:1.5rem;font-weight:800;line-height:1;">{{ $stats['total'] }}</div>
                                <div class="text-muted" style="font-size:.78rem;">Total Jurnal</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100" style="cursor:pointer;" onclick="setFilter('complete','all')">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:42px;height:42px;border-radius:10px;background:#dcfce7;
                                        display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <div style="font-size:1.5rem;font-weight:800;line-height:1;">{{ $stats['complete'] }}</div>
                                <div class="text-muted" style="font-size:.78rem;">Template Lengkap</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100" style="cursor:pointer;" onclick="setFilter('incomplete','all')">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:42px;height:42px;border-radius:10px;background:#fef9c3;
                                        display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-exclamation-triangle-fill" style="color:#ca8a04;font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <div style="font-size:1.5rem;font-weight:800;line-height:1;">{{ $stats['total'] - $stats['complete'] }}</div>
                                <div class="text-muted" style="font-size:.78rem;">Belum Lengkap</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100" style="cursor:pointer;" onclick="setFilter('all','auto')">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:42px;height:42px;border-radius:10px;background:#dcfce7;
                                        display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-lightning-charge-fill" style="color:#16a34a;font-size:1.2rem;"></i>
                            </div>
                            <div>
                                <div style="font-size:1.5rem;font-weight:800;line-height:1;">{{ $stats['auto'] }}</div>
                                <div class="text-muted" style="font-size:.78rem;">LOA Otomatis Aktif</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabel utama ────────────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header py-3">
                <div class="row g-2 align-items-center">
                    {{-- Judul --}}
                    <div class="col-12 col-md-auto me-auto">
                        <span class="fw-semibold">
                            <i class="bi bi-file-earmark-check-fill text-primary me-1"></i>
                            Master LOA — Template & Otomatis
                        </span>
                    </div>

                    {{-- Search --}}
                    <div class="col-12 col-md-5 col-lg-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="searchInput" class="form-control border-start-0 ps-0"
                                   placeholder="Cari nama jurnal atau kode…" autocomplete="off">
                            <button class="btn btn-outline-secondary" id="btnClearSearch" type="button"
                                    style="display:none;" title="Hapus pencarian">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Filter chips --}}
                <div class="d-flex flex-wrap gap-2 mt-3 align-items-center">
                    <span class="text-muted small me-1">Kelengkapan:</span>
                    <button class="btn btn-sm filter-chip active" data-filter-complete="all">Semua</button>
                    <button class="btn btn-sm filter-chip" data-filter-complete="complete">
                        <i class="bi bi-check-circle me-1"></i>Lengkap
                    </button>
                    <button class="btn btn-sm filter-chip" data-filter-complete="incomplete">
                        <i class="bi bi-exclamation-triangle me-1"></i>Belum Lengkap
                    </button>

                    <span class="text-muted small ms-3 me-1">LOA Otomatis:</span>
                    <button class="btn btn-sm filter-chip-auto active" data-filter-auto="all">Semua</button>
                    <button class="btn btn-sm filter-chip-auto" data-filter-auto="auto">
                        <i class="bi bi-lightning-charge-fill me-1"></i>Aktif
                    </button>
                    <button class="btn btn-sm filter-chip-auto" data-filter-auto="manual">
                        <i class="bi bi-hand-index me-1"></i>Manual
                    </button>

                    <span class="ms-auto text-muted small" id="rowCount"></span>
                    <button class="btn btn-sm btn-link text-muted p-0 ms-2" id="btnReset"
                            style="display:none;font-size:.78rem;" onclick="resetAllFilters()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset filter
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="loaTable">
                        <thead class="table-light">
                            <tr>
                                <th width="28" class="ps-3">No</th>
                                <th>Jurnal</th>
                                <th width="120">LOA Otomatis</th>
                                <th width="130">Kelengkapan</th>
                                <th width="90" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journals as $i => $j)
                            @php
                                $missing = [];
                                if (!$j->kode_singkat)          $missing[] = 'Kode singkat';
                                if (!$j->e_issn)                $missing[] = 'E-ISSN';
                                if (!$j->editor_name)           $missing[] = 'Nama editor';
                                if (!$j->logo_path)             $missing[] = 'Logo';
                                if (!$j->editor_signature_path) $missing[] = 'Tanda tangan';
                                $complete = count($missing) === 0;
                            @endphp
                            <tr class="loa-row"
                                data-complete="{{ $complete ? 'complete' : 'incomplete' }}"
                                data-auto="{{ $j->loa_auto_send ? 'auto' : 'manual' }}"
                                data-search="{{ strtolower($j->nama_jurnal . ' ' . $j->kode_singkat . ' ' . $j->editor_name . ' ' . $j->e_issn) }}">

                                <td class="ps-3 text-muted small">{{ $i + 1 }}</td>

                                <td>
                                    <div class="fw-semibold" style="font-size:.9rem;">{{ $j->nama_jurnal }}</div>
                                    <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                                        @if($j->kode_singkat)
                                        <span class="badge" style="background:{{ $j->primary_color ?? '#1A237E' }};font-size:.68rem;">
                                            {{ $j->kode_singkat }}
                                        </span>
                                        @endif
                                        @if($j->e_issn)
                                        <span class="badge bg-light text-dark border" style="font-size:.68rem;">E-ISSN: {{ $j->e_issn }}</span>
                                        @endif
                                        @if($j->p_issn)
                                        <span class="badge bg-light text-dark border" style="font-size:.68rem;">P-ISSN: {{ $j->p_issn }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    @if($j->loa_auto_send)
                                        <span class="badge bg-success" style="font-size:.7rem;">
                                            <i class="bi bi-lightning-charge-fill"></i> Aktif
                                        </span>
                                        @php
                                        $labels = \App\Http\Controllers\Admin\LoaMasterController::TRIGGER_OPTIONS;
                                        @endphp
                                        <div class="text-muted" style="font-size:.68rem;margin-top:2px;">
                                            {{ Str::after($labels[$j->loa_auto_trigger ?? 'manual'] ?? 'Manual', 'Setelah ') }}
                                        </div>
                                    @else
                                        <span class="badge bg-secondary" style="font-size:.7rem;">Manual</span>
                                    @endif
                                </td>

                                <td>
                                    @if($complete)
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Lengkap</span>
                                    @else
                                        <div>
                                            <span class="badge bg-warning text-dark"
                                                  title="{{ implode(', ', $missing) }} belum diisi"
                                                  data-bs-toggle="tooltip">
                                                <i class="bi bi-exclamation-triangle me-1"></i>{{ count($missing) }} kurang
                                            </span>
                                            <div class="text-muted" style="font-size:.66rem;margin-top:2px;">
                                                {{ implode(', ', array_slice($missing, 0, 2)) }}{{ count($missing) > 2 ? '…' : '' }}
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('admin.loa-master.edit', $j) }}"
                                           class="btn btn-sm btn-outline-primary" title="Setting LOA">
                                            <i class="bi bi-gear"></i> Setting
                                        </a>
                                        <a href="{{ route('admin.loa-master.preview-loa', $j) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Preview LOA"
                                           target="_blank">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2 opacity-50"></i>
                                    Belum ada jurnal aktif.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Empty state saat filter tidak ada hasil --}}
                <div id="emptyFilter" class="text-center py-5 text-muted" style="display:none;">
                    <i class="bi bi-funnel fs-3 d-block mb-2 opacity-50"></i>
                    <div>Tidak ada jurnal yang cocok dengan filter ini.</div>
                    <button class="btn btn-sm btn-link mt-1" onclick="resetAllFilters()">Reset filter</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Info panel ─────────────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="card border-0" style="background:#f8fafc;">
            <div class="card-body py-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex gap-3 align-items-start">
                            <i class="bi bi-1-circle-fill text-primary fs-4 flex-shrink-0"></i>
                            <div>
                                <div class="fw-semibold small">Setup Template</div>
                                <div class="text-muted" style="font-size:.8rem;">
                                    Klik <strong>Setting</strong> per jurnal untuk isi kode singkat, E-ISSN, editor, warna, logo, dan tanda tangan.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-3 align-items-start">
                            <i class="bi bi-2-circle-fill text-success fs-4 flex-shrink-0"></i>
                            <div>
                                <div class="fw-semibold small">Aktifkan LOA Otomatis</div>
                                <div class="text-muted" style="font-size:.8rem;">
                                    Pilih trigger (Production / Validator / Published). LOA email dikirim otomatis ke penulis saat step tersebut selesai.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-3 align-items-start">
                            <i class="bi bi-3-circle-fill text-warning fs-4 flex-shrink-0"></i>
                            <div>
                                <div class="fw-semibold small">Cetak / Request LOA</div>
                                <div class="text-muted" style="font-size:.8rem;">
                                    Admin: detail submission → tombol <strong>LOA</strong>.
                                    Penulis: buka <a href="{{ route('loa.request') }}" target="_blank">/request-loa</a> dan masukkan kode SIPERA.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.filter-chip, .filter-chip-auto {
    border: 1px solid #dee2e6;
    color: #6c757d;
    background: #fff;
    font-size: .78rem;
    padding: 3px 10px;
    border-radius: 20px;
    transition: all .15s;
}
.filter-chip:hover, .filter-chip-auto:hover {
    border-color: #4f46e5;
    color: #4f46e5;
}
.filter-chip.active {
    background: #4f46e5;
    border-color: #4f46e5;
    color: #fff;
}
.filter-chip-auto.active {
    background: #16a34a;
    border-color: #16a34a;
    color: #fff;
}
.loa-row { transition: opacity .1s; }
.loa-row.hidden { display: none; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var rows        = Array.from(document.querySelectorAll('.loa-row'));
    var searchInp   = document.getElementById('searchInput');
    var btnClear    = document.getElementById('btnClearSearch');
    var rowCountEl  = document.getElementById('rowCount');
    var emptyEl     = document.getElementById('emptyFilter');
    var btnReset    = document.getElementById('btnReset');

    var activeComplete = 'all';
    var activeAuto     = 'all';
    var activeSearch   = '';

    // ── Filter chips — kelengkapan ──────────────────────────────────
    document.querySelectorAll('.filter-chip').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-chip').forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            activeComplete = this.dataset.filterComplete;
            applyFilters();
        });
    });

    // ── Filter chips — LOA otomatis ─────────────────────────────────
    document.querySelectorAll('.filter-chip-auto').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-chip-auto').forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            activeAuto = this.dataset.filterAuto;
            applyFilters();
        });
    });

    // ── Search ──────────────────────────────────────────────────────
    searchInp.addEventListener('input', function () {
        activeSearch = this.value.trim().toLowerCase();
        btnClear.style.display = activeSearch ? '' : 'none';
        applyFilters();
    });
    btnClear.addEventListener('click', function () {
        searchInp.value = '';
        activeSearch = '';
        btnClear.style.display = 'none';
        applyFilters();
        searchInp.focus();
    });

    // ── Apply ───────────────────────────────────────────────────────
    function applyFilters() {
        var visible = 0;
        rows.forEach(function (row) {
            var matchC = activeComplete === 'all' || row.dataset.complete === activeComplete;
            var matchA = activeAuto     === 'all' || row.dataset.auto     === activeAuto;
            var matchS = !activeSearch || row.dataset.search.includes(activeSearch);
            var show   = matchC && matchA && matchS;
            row.classList.toggle('hidden', !show);
            if (show) visible++;
        });

        var total = rows.length;
        rowCountEl.textContent = visible < total
            ? visible + ' dari ' + total + ' jurnal'
            : total + ' jurnal';

        emptyEl.style.display = (visible === 0 && rows.length > 0) ? '' : 'none';

        var isFiltered = activeComplete !== 'all' || activeAuto !== 'all' || activeSearch !== '';
        btnReset.style.display = isFiltered ? '' : 'none';
    }

    // ── Reset ───────────────────────────────────────────────────────
    window.resetAllFilters = function () {
        activeComplete = 'all';
        activeAuto     = 'all';
        activeSearch   = '';
        searchInp.value = '';
        btnClear.style.display = 'none';
        document.querySelectorAll('.filter-chip').forEach(function (b) {
            b.classList.toggle('active', b.dataset.filterComplete === 'all');
        });
        document.querySelectorAll('.filter-chip-auto').forEach(function (b) {
            b.classList.toggle('active', b.dataset.filterAuto === 'all');
        });
        applyFilters();
    };

    // ── setFilter dari stat card click ──────────────────────────────
    window.setFilter = function (complete, auto) {
        activeComplete = complete;
        activeAuto     = auto;
        activeSearch   = '';
        searchInp.value = '';
        btnClear.style.display = 'none';
        document.querySelectorAll('.filter-chip').forEach(function (b) {
            b.classList.toggle('active', b.dataset.filterComplete === complete);
        });
        document.querySelectorAll('.filter-chip-auto').forEach(function (b) {
            b.classList.toggle('active', b.dataset.filterAuto === auto);
        });
        applyFilters();
        document.getElementById('loaTable')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    // Tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

    // Initial count
    applyFilters();
})();
</script>
@endpush
