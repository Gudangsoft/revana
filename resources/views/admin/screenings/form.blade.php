@extends('layouts.app')
@section('title', 'Screening Awal — ' . $submission->kode_submit)
@section('page-title', 'Screening Awal Artikel')
@section('sidebar')@include('admin.partials.sidebar')@endsection

@section('content')
@php
use App\Http\Controllers\Admin\ScreeningFormController;
$cl = $screening?->checklist ?? [];
$totalItems = 0;
$totalPassed = 0;
foreach($definition as $sec) { foreach($sec['items'] as $k=>$l) { $totalItems++; if(($cl[$k] ?? null) === true) $totalPassed++; } }
@endphp
<style>
.section-card { border-left: 4px solid #e5e7eb; }
.section-card.border-success { border-left-color: #22c55e !important; }
.section-card.border-warning { border-left-color: #f59e0b !important; }
.section-card.border-danger  { border-left-color: #ef4444 !important; }
.check-row { display: flex; align-items: center; gap: 8px; padding: 6px 0; border-bottom: 1px solid #f1f5f9; }
.check-row:last-child { border-bottom: none; }
.check-label { flex: 1; font-size: 0.82rem; }
.tri-btn { width: 32px; height: 26px; padding: 0; font-size: 0.72rem; border-radius: 4px; }
.preset-item { cursor: pointer; font-size: 0.78rem; padding: 4px 8px; border-radius: 4px; border: 1px solid #e2e8f0; margin-bottom: 3px; line-height: 1.3; transition: background 0.12s; }
.preset-item:hover { background: #f0fdf4; border-color: #86efac; }
.preset-item.revisi:hover { background: #fffbeb; border-color: #fcd34d; }
.preset-item.selected { background: #dcfce7; border-color: #4ade80; }
.score-ring { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; }
</style>

<div class="container-fluid">
    <div class="mb-3">
        <a href="{{ route('admin.submissions.monitoring') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Monitoring
        </a>
        @if($screening)
        <a href="{{ route('admin.screenings.show', $submission) }}" class="btn btn-outline-info btn-sm ms-1">
            <i class="bi bi-eye"></i> Lihat Hasil
        </a>
        @endif
    </div>

    {{-- Header submission --}}
    <div class="card mb-3 border-0 bg-light">
        <div class="card-body py-2 px-3">
            <div class="row align-items-center">
                <div class="col-auto">
                    <code class="text-primary fs-6">{{ $submission->kode_submit }}</code>
                </div>
                <div class="col">
                    <div class="fw-semibold" style="font-size:0.9rem;">{{ $submission->judul_artikel }}</div>
                    <small class="text-muted">Penulis: {{ $submission->nama_penulis ?? '-' }}</small>
                </div>
                <div class="col-auto">
                    <div class="score-ring {{ $totalPassed >= $totalItems * 0.8 ? 'bg-success text-white' : ($totalPassed >= $totalItems * 0.5 ? 'bg-warning' : 'bg-danger text-white') }}"
                         id="scoreRing" title="Item terpenuhi">
                        <span id="scoreNum">{{ $totalPassed }}</span>/<span id="scoreDen">{{ $totalItems }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST"
          action="{{ $screening ? route('admin.screenings.update', [$submission, $screening]) : route('admin.screenings.store', $submission) }}">
        @csrf
        @if($screening) @method('PUT') @endif

        <div class="row g-3">
            {{-- Kiri: checklist sections --}}
            <div class="col-lg-7">
                @foreach($definition as $secKey => $section)
                @php
                    $secItems = $section['items'];
                    $secPass  = collect($secItems)->keys()->filter(fn($k) => ($cl[$k] ?? null) === true)->count();
                    $secTotal = count($secItems);
                    $secColor = $secPass === $secTotal ? 'success' : ($secPass >= $secTotal/2 ? 'warning' : 'danger');
                @endphp
                <div class="card mb-2 section-card border-{{ $secColor }}" id="sec-{{ $secKey }}">
                    <div class="card-header py-2 d-flex align-items-center justify-content-between" style="cursor:pointer"
                         onclick="toggleSection('{{ $secKey }}')">
                        <div>
                            <i class="bi {{ $section['icon'] }} me-2 text-muted"></i>
                            <strong style="font-size:0.85rem;">{{ $secKey }}. {{ $section['title'] }}</strong>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-{{ $secColor }}" id="badge-{{ $secKey }}">{{ $secPass }}/{{ $secTotal }}</span>
                            <i class="bi bi-chevron-down text-muted" id="chev-{{ $secKey }}"></i>
                        </div>
                    </div>
                    <div class="card-body py-2 px-3" id="body-{{ $secKey }}">
                        @foreach($secItems as $itemKey => $itemLabel)
                        @php $curVal = $cl[$itemKey] ?? null; @endphp
                        <div class="check-row" id="row-{{ $itemKey }}">
                            <span class="check-label">{{ $itemLabel }}</span>
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="hidden" name="checklist[{{ $itemKey }}]" id="inp-{{ $itemKey }}"
                                       value="{{ $curVal === true ? '1' : ($curVal === false ? '0' : '') }}">
                                <button type="button" class="tri-btn btn {{ $curVal === true ? 'btn-success' : 'btn-outline-secondary' }}"
                                        onclick="setCheck('{{ $itemKey }}', 1, this)" title="Ya / Terpenuhi">✓</button>
                                <button type="button" class="tri-btn btn {{ $curVal === false ? 'btn-danger' : 'btn-outline-secondary' }}"
                                        onclick="setCheck('{{ $itemKey }}', 0, this)" title="Tidak / Belum terpenuhi">✗</button>
                                <button type="button" class="tri-btn btn {{ $curVal === null ? 'btn-secondary' : 'btn-outline-secondary' }}"
                                        onclick="setCheck('{{ $itemKey }}', null, this)" title="N/A">—</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                {{-- Similarity score --}}
                <div class="card mb-2">
                    <div class="card-body py-2 px-3">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.85rem;">
                            <i class="bi bi-percent text-warning"></i> Similarity Score (Turnitin/iThenticate)
                        </label>
                        <div class="input-group input-group-sm" style="max-width:200px;">
                            <input type="number" name="similarity_score" class="form-control" step="0.01" min="0" max="100"
                                   value="{{ old('similarity_score', $screening?->similarity_score) }}" placeholder="e.g. 18.5">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Batas aman: &lt; 25%</small>
                    </div>
                </div>
            </div>

            {{-- Kanan: keputusan + catatan --}}
            <div class="col-lg-5">
                {{-- Email penulis --}}
                <div class="card mb-3">
                    <div class="card-header py-2"><strong style="font-size:0.82rem;"><i class="bi bi-envelope-at text-primary"></i> Email Penulis</strong></div>
                    <div class="card-body py-2 px-3">
                        <input type="email" name="recipient_email" class="form-control form-control-sm"
                               value="{{ old('recipient_email', $screening?->recipient_email ?? $defaultEmail ?? '') }}"
                               placeholder="email@penulis.com">
                        <div class="form-text">Digunakan untuk kirim notifikasi hasil screening.</div>
                    </div>
                </div>

                {{-- Keputusan --}}
                <div class="card mb-3">
                    <div class="card-header py-2"><strong style="font-size:0.82rem;"><i class="bi bi-gavel text-warning"></i> Keputusan Screening</strong></div>
                    <div class="card-body py-2 px-3">
                        @foreach(['diterima'=>['success','check-circle-fill','Diterima (Proceed to Review)'], 'revisi'=>['warning','arrow-clockwise','Perlu Revisi Awal'], 'ditolak'=>['danger','x-circle-fill','Ditolak (Desk Reject)']] as $val => [$color, $icon, $label])
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="keputusan" id="k-{{ $val }}" value="{{ $val }}"
                                   {{ old('keputusan', $screening?->keputusan) === $val ? 'checked' : '' }}
                                   onchange="onKeputusanChange('{{ $val }}')">
                            <label class="form-check-label" for="k-{{ $val }}">
                                <span class="badge bg-{{ $color }}"><i class="bi bi-{{ $icon }}"></i> {{ $label }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Catatan editor --}}
                <div class="card mb-3">
                    <div class="card-header py-2 d-flex align-items-center justify-content-between">
                        <strong style="font-size:0.82rem;"><i class="bi bi-pencil-square text-info"></i> Catatan Editor</strong>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary btn-sm active" onclick="filterPreset('semua',this)">Semua</button>
                            <button type="button" class="btn btn-outline-success btn-sm"   onclick="filterPreset('diterima',this)">Diterima</button>
                            <button type="button" class="btn btn-outline-warning btn-sm"   onclick="filterPreset('revisi',this)">Revisi</button>
                        </div>
                    </div>
                    <div class="card-body py-2 px-3">
                        <input type="text" id="presetSearch" class="form-control form-control-sm mb-2"
                               placeholder="Cari catatan..." oninput="searchPreset(this.value)">
                        <div id="presetList" style="max-height:200px; overflow-y:auto; margin-bottom:8px;">
                            @foreach($presets['diterima'] as $no => $text)
                                <div class="preset-item diterima" data-cat="diterima" onclick="appendCatatan({{ json_encode($text) }}, this)">
                                    <span class="text-muted me-1" style="font-size:0.7rem;">{{ $no }}.</span>{{ $text }}
                                </div>
                            @endforeach
                            @foreach($presets['revisi'] as $no => $text)
                                <div class="preset-item revisi" data-cat="revisi" onclick="appendCatatan({{ json_encode($text) }}, this)">
                                    <span class="text-muted me-1" style="font-size:0.7rem;">{{ $no }}.</span>{{ $text }}
                                </div>
                            @endforeach
                        </div>
                        <textarea name="catatan" id="catatanInput" class="form-control" rows="7"
                                  placeholder="Isi catatan editor, atau klik item di atas untuk menambahkan...">{{ old('catatan', $screening?->catatan) }}</textarea>
                        <div class="d-flex justify-content-end mt-1">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('catatanInput').value=''">
                                <i class="bi bi-trash"></i> Bersihkan
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Submit buttons --}}
                <div class="d-grid gap-2">
                    <button type="submit" name="save_only" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Screening
                    </button>
                    <button type="submit" name="send_email" value="1" class="btn btn-success">
                        <i class="bi bi-send"></i> Simpan & Kirim Email ke Penulis
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// ── Tri-state checklist ──────────────────────────────────────────────
var sectionItems = @json(collect($definition)->map(fn($s) => array_keys($s['items'])));
var sectionKeys  = @json(array_keys($definition));

function setCheck(key, val, btn) {
    var inp = document.getElementById('inp-' + key);
    inp.value = (val === 1) ? '1' : (val === 0 ? '0' : '');
    var btns = btn.closest('.btn-group').querySelectorAll('.tri-btn');
    btns[0].className = 'tri-btn btn ' + (val === 1 ? 'btn-success' : 'btn-outline-secondary');
    btns[1].className = 'tri-btn btn ' + (val === 0 ? 'btn-danger'  : 'btn-outline-secondary');
    btns[2].className = 'tri-btn btn ' + (val === null ? 'btn-secondary' : 'btn-outline-secondary');
    updateSectionBadge(key);
    updateScoreRing();
}

function getSectionForKey(key) {
    for (var sec in sectionItems) {
        if (sectionItems[sec].indexOf(key) !== -1) return sectionKeys[sec];
    }
    return null;
}

function updateSectionBadge(key) {
    var sec = getSectionForKey(key);
    if (!sec) return;
    var items  = sectionItems[sectionKeys.indexOf(sec)];
    var passed = 0;
    items.forEach(function(k) {
        var v = document.getElementById('inp-' + k)?.value;
        if (v === '1') passed++;
    });
    var badge = document.getElementById('badge-' + sec);
    if (badge) {
        badge.textContent = passed + '/' + items.length;
        badge.className = 'badge bg-' + (passed === items.length ? 'success' : (passed >= items.length/2 ? 'warning' : 'danger'));
    }
    var card = document.getElementById('sec-' + sec);
    if (card) {
        card.classList.remove('border-success','border-warning','border-danger');
        card.classList.add('border-' + (passed === items.length ? 'success' : (passed >= items.length/2 ? 'warning' : 'danger')));
    }
}

function updateScoreRing() {
    var total = 0, passed = 0;
    sectionItems.forEach(function(items) {
        items.forEach(function(k) {
            total++;
            var v = document.getElementById('inp-' + k)?.value;
            if (v === '1') passed++;
        });
    });
    document.getElementById('scoreNum').textContent = passed;
    document.getElementById('scoreDen').textContent = total;
    var ring = document.getElementById('scoreRing');
    ring.className = 'score-ring ' + (passed >= total * 0.8 ? 'bg-success text-white' : (passed >= total * 0.5 ? 'bg-warning' : 'bg-danger text-white'));
}

// ── Section collapse ─────────────────────────────────────────────────
function toggleSection(sec) {
    var body = document.getElementById('body-' + sec);
    var chev = document.getElementById('chev-' + sec);
    if (body.style.display === 'none') {
        body.style.display = '';
        chev.className = 'bi bi-chevron-down text-muted';
    } else {
        body.style.display = 'none';
        chev.className = 'bi bi-chevron-right text-muted';
    }
}

// ── Catatan presets ──────────────────────────────────────────────────
var currentFilter = 'semua';

function filterPreset(cat, btn) {
    currentFilter = cat;
    document.querySelectorAll('#presetList .preset-item').forEach(function(el) {
        var match = cat === 'semua' || el.dataset.cat === cat;
        el.style.display = match ? '' : 'none';
    });
    btn.closest('.btn-group').querySelectorAll('.btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

function searchPreset(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#presetList .preset-item').forEach(function(el) {
        var catMatch = currentFilter === 'semua' || el.dataset.cat === currentFilter;
        el.style.display = (catMatch && el.textContent.toLowerCase().includes(q)) ? '' : 'none';
    });
}

function appendCatatan(text, el) {
    var ta = document.getElementById('catatanInput');
    if (ta.value && !ta.value.endsWith('\n')) ta.value += '\n';
    ta.value += text;
    el.classList.toggle('selected');
}

function onKeputusanChange(val) {
    if (val === 'diterima') filterPreset('diterima', document.querySelector('[onclick*="diterima"]'));
    else if (val === 'revisi' || val === 'ditolak') filterPreset('revisi', document.querySelector('[onclick*="revisi"]'));
}

// Auto-filter preset saat halaman load jika sudah ada keputusan
window.addEventListener('DOMContentLoaded', function() {
    var checked = document.querySelector('input[name="keputusan"]:checked');
    if (checked) onKeputusanChange(checked.value);
});
</script>
@endsection
