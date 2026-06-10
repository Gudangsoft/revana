@extends('layouts.app')

@section('title', 'Pengaturan Point')
@section('page-title', 'Pengaturan Point PIC & Marketing')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="bi bi-exclamation-circle-fill me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Initialize Alert --}}
@if($missingPic->isNotEmpty() || $missingMarketing->isNotEmpty())
<div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0"></i>
    <div class="flex-grow-1">
        <strong>Ada step yang belum dikonfigurasi:</strong>
        @if($missingPic->isNotEmpty())
            PIC — {{ $missingPic->join(', ') }}.
        @endif
        @if($missingMarketing->isNotEmpty())
            Marketing — {{ $missingMarketing->join(', ') }}.
        @endif
        Step ini akan menggunakan fallback <strong>1 pt</strong> sampai diinisialisasi.
    </div>
    <form method="POST" action="{{ route('admin.task-point-settings.init-defaults') }}" class="flex-shrink-0">
        @csrf
        <button type="submit" class="btn btn-warning btn-sm">
            <i class="bi bi-magic"></i> Inisialisasi Default (1 pt)
        </button>
    </form>
</div>
@endif

{{-- ================================================================
     FORMULA PREVIEW (live update via JS)
     ================================================================ --}}
<div class="row g-3 mb-4">
    {{-- PIC Formula --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center" style="background:#e0e7ff; border-bottom:2px solid #818cf8;">
                <span class="fw-semibold text-primary"><i class="bi bi-diagram-3-fill me-1"></i> Formula Point PIC — Alur Lengkap</span>
                <span class="badge fs-6 text-bg-primary" id="picTotalBadge">— pt</span>
            </div>
            <div class="card-body py-3">
                <div class="d-flex flex-wrap gap-2 align-items-center" id="picFormulaLine">
                    <span class="text-muted fst-italic">Memuat...</span>
                </div>
                <div class="mt-3 d-flex gap-3 flex-wrap" style="font-size:.8rem; color:#64748b;">
                    <span><i class="bi bi-circle-fill text-primary"></i> Step aktif — point diberikan</span>
                    <span><i class="bi bi-circle text-secondary"></i> Step nonaktif — tidak dihitung dalam total</span>
                </div>
            </div>
        </div>
    </div>
    {{-- Marketing Formula --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center" style="background:#dcfce7; border-bottom:2px solid #4ade80;">
                <span class="fw-semibold text-success"><i class="bi bi-megaphone-fill me-1"></i> Formula Marketing</span>
                <span class="badge fs-6 text-bg-success" id="mktTotalBadge">— pt</span>
            </div>
            <div class="card-body text-center d-flex flex-column justify-content-center gap-2">
                <div style="font-size:.85rem; color:#64748b;">Setiap <strong>1 submission berhasil</strong> memberikan:</div>
                <div class="display-5 fw-bold text-success" id="mktPointDisplay">—</div>
                <div style="font-size:.8rem; color:#64748b;">point ke akun Marketing yang merujuk</div>
                <div class="badge text-bg-light border mt-1" style="font-size:.8rem;" id="mktFormulaText">
                    1 Submission = — pt Marketing
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================
     MAIN FORM
     ================================================================ --}}
<form action="{{ route('admin.task-point-settings.update') }}" method="POST" id="settingsForm">
    @csrf
    @method('PUT')

    {{-- === PIC SETTINGS === --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-2" style="background:#e0e7ff; border-bottom:2px solid #818cf8;">
            <div>
                <i class="bi bi-person-badge-fill text-primary me-1"></i>
                <strong class="text-primary">Point PIC per Tahap</strong>
            </div>
            <small class="text-muted">Point diberikan saat tahap selesai divalidasi</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="picTable">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th width="48" class="text-center text-muted">#</th>
                        <th width="110">Step</th>
                        <th>Label Tugas <small class="text-muted fw-normal">(klik untuk edit)</small></th>
                        <th width="160" class="text-center">Point per Tugas</th>
                        <th width="80" class="text-center">Aktif</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($picOrder as $stepIdx => $stepKey)
                    @php $setting = $picByKey->get($stepKey); @endphp
                    @if($setting)
                    <tr class="step-row {{ $setting->is_active ? '' : 'inactive-row' }}" data-step="{{ $stepKey }}">
                        <td class="text-center">
                            <span class="badge rounded-pill {{ $setting->is_active ? 'text-bg-primary' : 'text-bg-secondary' }} step-num-badge">
                                {{ $stepIdx + 1 }}
                            </span>
                        </td>
                        <td>
                            <code class="text-primary" style="font-size:.8rem;">{{ $stepKey }}</code>
                        </td>
                        <td>
                            <input type="text"
                                   name="task_label[{{ $setting->id }}]"
                                   value="{{ $setting->task_label }}"
                                   class="form-control form-control-sm inline-label-input"
                                   placeholder="Label tahap..."
                                   maxlength="100">
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <input type="number"
                                       name="points[{{ $setting->id }}]"
                                       value="{{ $setting->points }}"
                                       class="form-control form-control-sm text-center pic-point-input"
                                       data-step="{{ $stepKey }}"
                                       min="0" step="0.01"
                                       style="width:90px;">
                                <small class="text-muted">pt</small>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center mb-0">
                                <input type="checkbox"
                                       class="form-check-input pic-active-toggle"
                                       name="is_active[{{ $setting->id }}]"
                                       data-step="{{ $stepKey }}"
                                       value="1"
                                       role="switch"
                                       {{ $setting->is_active ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    @else
                    <tr class="table-light step-row-missing">
                        <td class="text-center">
                            <span class="badge rounded-pill text-bg-secondary opacity-50">{{ $stepIdx + 1 }}</span>
                        </td>
                        <td><code class="text-secondary">{{ $stepKey }}</code></td>
                        <td colspan="3" class="text-muted fst-italic" style="font-size:.83rem;">
                            <i class="bi bi-exclamation-circle me-1 text-warning"></i>
                            Belum dikonfigurasi — sistem menggunakan fallback <strong>1 pt</strong>.
                            Klik <strong>Inisialisasi Default</strong> di atas.
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                <tfoot style="background:#f8fafc;">
                    <tr>
                        <td colspan="3" class="text-end pe-3 fw-semibold text-muted">Total point PIC untuk alur lengkap:</td>
                        <td class="text-center">
                            <strong class="text-primary fs-5" id="picTableTotal">—</strong>
                            <small class="text-muted"> pt</small>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- === MARKETING SETTINGS === --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-2" style="background:#dcfce7; border-bottom:2px solid #4ade80;">
            <div>
                <i class="bi bi-megaphone-fill text-success me-1"></i>
                <strong class="text-success">Point Marketing per Submission</strong>
            </div>
            <small class="text-muted">Point diberikan saat submission baru masuk dengan kode Marketing</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th width="110">Step</th>
                        <th>Label</th>
                        <th width="160" class="text-center">Point per Submission</th>
                        <th width="80" class="text-center">Aktif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($marketingSettings as $setting)
                    <tr>
                        <td><code class="text-success" style="font-size:.8rem;">{{ $setting->task_key }}</code></td>
                        <td>
                            <input type="text"
                                   name="task_label[{{ $setting->id }}]"
                                   value="{{ $setting->task_label }}"
                                   class="form-control form-control-sm inline-label-input"
                                   maxlength="100">
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <input type="number"
                                       name="points[{{ $setting->id }}]"
                                       value="{{ $setting->points }}"
                                       class="form-control form-control-sm text-center marketing-point-input"
                                       min="0" step="0.01"
                                       style="width:90px;">
                                <small class="text-muted">pt</small>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center mb-0">
                                <input type="checkbox"
                                       class="form-check-input"
                                       name="is_active[{{ $setting->id }}]"
                                       value="1"
                                       role="switch"
                                       {{ $setting->is_active ? 'checked' : '' }}>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3 fst-italic">
                            Belum ada setting Marketing — klik <strong>Inisialisasi Default</strong> di atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- === FOOTER === --}}
    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
        <div class="alert alert-info mb-0 py-2 px-3" style="font-size:.82rem; max-width:680px;">
            <i class="bi bi-lightbulb-fill me-1"></i>
            <strong>Perubahan point hanya berlaku untuk transaksi baru ke depan.</strong>
            Data historis tidak berubah otomatis.
            Gunakan tombol <strong>Sync Ulang Poin</strong> di halaman laporan point untuk recalculate:
            <a href="{{ route('admin.pic-points.index') }}" class="alert-link" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Laporan Poin PIC
            </a>
            &nbsp;|&nbsp;
            <a href="{{ route('admin.marketing-points.index') }}" class="alert-link" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Laporan Poin Marketing
            </a>
        </div>
        <button type="submit" class="btn btn-primary px-5 flex-shrink-0">
            <i class="bi bi-save me-1"></i> Simpan Pengaturan
        </button>
    </div>
</form>

<style>
.inline-label-input {
    border-color: transparent !important;
    background: transparent !important;
    font-weight: 500;
    transition: border-color .15s, background .15s;
}
.inline-label-input:hover {
    border-color: #ced4da !important;
    background: #fff !important;
}
.inline-label-input:focus {
    border-color: #86b7fe !important;
    background: #fff !important;
    box-shadow: 0 0 0 2px rgba(13,110,253,.15) !important;
}
.inactive-row {
    opacity: .65;
    background: #fafafa;
}
.inactive-row .pic-point-input {
    text-decoration: line-through;
    color: #94a3b8;
}
.formula-badge {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: .75rem;
    line-height: 1.2;
    min-width: 68px;
    text-align: center;
}
.formula-badge .fb-label { font-weight: 600; }
.formula-badge .fb-pts { font-size: .85rem; font-weight: 700; }
.formula-op { font-size: 1.1rem; color: #94a3b8; font-weight: 300; }
</style>
@endsection

@section('scripts')
@php
    $picDataJs = $picByKey->map(fn($s) => [
        'label'  => $s->task_label,
        'points' => (float) $s->points,
        'active' => (bool)  $s->is_active,
    ]);
    $picDefaultsJs = array_combine(
        $picOrder,
        array_map(fn($k) => ['label' => $k, 'points' => 1, 'active' => false], $picOrder)
    );
@endphp
<script>
const PIC_STEPS = @json($picOrder);
const PIC_STEP_DEFAULTS = @json($picDefaultsJs);
const PIC_INIT_DATA = @json($picDataJs);

// Merge defaults with actual DB data
Object.keys(PIC_STEP_DEFAULTS).forEach(k => {
    if (PIC_INIT_DATA[k]) PIC_STEP_DEFAULTS[k] = PIC_INIT_DATA[k];
});

const STEP_COLORS = {
    submit: '#64748b', editor1: '#0ea5e9', author1: '#f59e0b',
    editor2: '#0ea5e9', reviewer1: '#8b5cf6', reviewer2: '#8b5cf6',
    editor3: '#0ea5e9', author2: '#f59e0b', production: '#22c55e'
};

function readCurrentValues() {
    const vals = {};
    PIC_STEPS.forEach(step => {
        const ptEl  = document.querySelector(`.pic-point-input[data-step="${step}"]`);
        const actEl = document.querySelector(`.pic-active-toggle[data-step="${step}"]`);
        vals[step] = {
            label:  ptEl ? (ptEl.closest('tr').querySelector('.inline-label-input')?.value || step) : (PIC_STEP_DEFAULTS[step]?.label || step),
            points: ptEl  ? (parseFloat(ptEl.value)  || 0)  : (PIC_STEP_DEFAULTS[step]?.points || 1),
            active: actEl ? actEl.checked : (PIC_STEP_DEFAULTS[step]?.active || false),
        };
    });
    return vals;
}

function updateFormula() {
    const vals = readCurrentValues();

    // --- PIC formula line ---
    const formulaEl = document.getElementById('picFormulaLine');
    const totalEl   = document.getElementById('picTotalBadge');
    const tableTotal = document.getElementById('picTableTotal');

    let total = 0;
    const parts = [];
    PIC_STEPS.forEach(step => {
        const v = vals[step];
        const color = STEP_COLORS[step] || '#64748b';
        if (v.active) {
            total += v.points;
            const pts = v.points % 1 === 0 ? v.points : v.points.toFixed(2);
            parts.push(`<span class="formula-badge" style="background:${color}18; border:1.5px solid ${color}40; color:${color};">
                <span class="fb-label">${escHtml(v.label)}</span>
                <span class="fb-pts">${pts} pt</span>
            </span>`);
        } else {
            parts.push(`<span class="formula-badge" style="background:#f1f5f9; border:1.5px dashed #cbd5e1; color:#94a3b8;">
                <span class="fb-label" style="text-decoration:line-through;">${escHtml(v.label)}</span>
                <span class="fb-pts" style="color:#cbd5e1;">off</span>
            </span>`);
        }
        if (parts.length < PIC_STEPS.length) parts.push('<span class="formula-op">+</span>');
    });

    const fmtTotal = total % 1 === 0 ? total : total.toFixed(2);
    parts.push('<span class="formula-op">=</span>');
    parts.push(`<span class="formula-badge" style="background:#eff6ff; border:2px solid #818cf8; color:#3730a3; min-width:80px;">
        <span class="fb-label">Total</span>
        <span class="fb-pts fs-6">${fmtTotal} pt</span>
    </span>`);

    formulaEl.innerHTML = parts.join(' ');
    totalEl.textContent  = fmtTotal + ' pt';
    if (tableTotal) tableTotal.textContent = fmtTotal;

    // --- Inactive row styling ---
    PIC_STEPS.forEach(step => {
        const row = document.querySelector(`.step-row[data-step="${step}"]`);
        if (!row) return;
        const active = vals[step].active;
        row.classList.toggle('inactive-row', !active);
        const numBadge = row.querySelector('.step-num-badge');
        if (numBadge) {
            numBadge.className = 'badge rounded-pill step-num-badge ' + (active ? 'text-bg-primary' : 'text-bg-secondary');
        }
    });

    // --- Marketing formula ---
    const mktEl   = document.querySelector('.marketing-point-input');
    const mktPts  = mktEl ? (parseFloat(mktEl.value) || 0) : 0;
    const fmtMkt  = mktPts % 1 === 0 ? mktPts : mktPts.toFixed(2);
    document.getElementById('mktPointDisplay').textContent = fmtMkt;
    document.getElementById('mktTotalBadge').textContent   = fmtMkt + ' pt';
    document.getElementById('mktFormulaText').textContent  = `1 Submission = ${fmtMkt} pt Marketing`;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Attach listeners
document.querySelectorAll('.pic-point-input, .pic-active-toggle, .marketing-point-input').forEach(el => {
    el.addEventListener('input',  updateFormula);
    el.addEventListener('change', updateFormula);
});

// Init on load
updateFormula();
</script>
@endsection
