@extends('layouts.app')

@section('title', ($pageTitle ?? 'Monitoring') . ' — ' . $appSettings['app_name'])
@section('page-title', $pageTitle ?? 'Monitoring')

@section('content')
<style>
.monitoring-scroll-wrapper {
    overflow-x: auto;
    overflow-y: visible;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    scrollbar-width: thin;
    scrollbar-color: #6366f1 #dee2e6;
}
.monitoring-scroll-wrapper::-webkit-scrollbar { height: 12px; }
.monitoring-scroll-wrapper::-webkit-scrollbar-track { background: #e9ecef; border-radius: 6px; }
.monitoring-scroll-wrapper::-webkit-scrollbar-thumb { background: #6366f1; border-radius: 6px; border: 3px solid #e9ecef; }

.table-monitoring { border-collapse: collapse; font-size: 0.72rem; line-height: 1.2; }
.table-monitoring thead th {
    position: sticky; top: 0; z-index: 3;
    white-space: nowrap; padding: 4px 8px; height: 30px;
    vertical-align: middle; font-size: 0.68rem; font-weight: 700;
    text-transform: uppercase; border: 1px solid #0a0e1a !important;
}
.table-monitoring thead tr:first-child th {
    background: #0f172a !important; color: #cbd5e1 !important;
}
.table-monitoring thead th.bg-primary  { background:#3730a3 !important;color:#c7d2fe !important;border-left:4px solid #818cf8 !important; }
.table-monitoring thead tr:nth-child(2) th {
    top: 30px; height: 26px; font-size: 0.65rem; font-weight: 700;
    background: #f1f5f9 !important; color: #334155 !important;
    border-top: 2px solid #0a0e1a !important;
}
.table-monitoring thead tr:nth-child(2) th.bg-primary { background:#c7d2fe !important;color:#3730a3 !important;border-left:4px solid #6366f1 !important; }
.table-monitoring thead th.sticky-first, .table-monitoring td.sticky-first {
    position: sticky; left: 0; z-index: 2;
    background: #fff; min-width: 130px;
    box-shadow: 2px 0 4px -2px rgba(0,0,0,.12);
}
.table-monitoring thead th.sticky-first { z-index: 6; background: #0f172a !important; }
.table-monitoring tbody td {
    white-space: nowrap; padding: 3px 6px;
    border: 1px solid #dee2e6; vertical-align: middle;
}
.table-monitoring tbody tr:hover td { background-color: #e8f4fd !important; }
.row-published td { background-color: #d1fae5 !important; }
.row-rejected  td { background-color: #fee2e2 !important; }
.row-pending   td { background-color: #fffbeb !important; }
.row-progress  td { background-color: #f0f7ff !important; }
.table-monitoring tbody tr:hover td.sticky-first { background-color: #e8f4fd !important; }

.status-badge {
    display: inline-block; padding: 2px 6px; border-radius: 10px;
    font-size: 0.62rem; font-weight: 600;
}
.inline-assign-select {
    font-size: 0.7rem; padding: 2px 4px; min-width: 90px; max-width: 120px;
    border: 1px solid #dee2e6; border-radius: 4px; background: #fff; cursor: pointer;
}
.inline-assign-select.has-value { background-color: #d1e7dd; border-color: #198754; }
.inline-assign-select.saving { opacity: .6; pointer-events: none; }
.inline-credential-input {
    font-size: 0.65rem; padding: 2px 4px; width: 70px;
    border: 1px solid #dee2e6; border-radius: 3px;
    background: #fff; font-family: monospace;
}
.inline-credential-input.has-value { background-color: #fff3cd; }
.inline-credential-input.saving { opacity: .6; }
.credential-group { display: flex; gap: 2px; align-items: center; }
</style>

{{-- Filter bar --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.pic-reviewer.monitoring') }}" class="row g-2 align-items-end">
            <input type="hidden" name="program" value="{{ $program }}">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Cari</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Kode, judul, penulis..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Jurnal</label>
                <select name="journal_master_id" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    @foreach($journals as $j)
                        <option value="{{ $j->id }}" {{ request('journal_master_id') == $j->id ? 'selected' : '' }}>
                            {{ Str::limit($j->nama_jurnal, 22) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    @foreach($statusOptions as $k => $v)
                        <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Urutan</label>
                <select name="sort_by" class="form-select form-select-sm">
                    <option value="date_asc"  {{ request('sort_by','date_asc') == 'date_asc'  ? 'selected':'' }}>↑ Terlama</option>
                    <option value="date_desc" {{ request('sort_by') == 'date_desc' ? 'selected':'' }}>↓ Terbaru</option>
                    <option value="title_asc" {{ request('sort_by') == 'title_asc' ? 'selected':'' }}>↑ Judul A-Z</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2 align-items-end">
                <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('admin.pic-reviewer.monitoring', array_filter(['program' => $program ?: null])) }}"
                   class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Info total --}}
<div class="d-flex align-items-center justify-content-between mb-2">
    <small class="text-muted">
        Menampilkan <strong>{{ $submissions->firstItem() }}–{{ $submissions->lastItem() }}</strong>
        dari <strong>{{ $submissions->total() }}</strong> submission
    </small>
    @include('partials.per-page-selector', ['paginator' => $submissions, 'default' => 50])
</div>

{{-- Tabel monitoring --}}
<div class="monitoring-scroll-wrapper">
    <table class="table table-monitoring table-bordered">
        <thead>
            <tr>
                <th rowspan="2" class="align-middle sticky-first">Kode Submit</th>
                <th rowspan="2" class="align-middle text-center" style="min-width:80px;">Status</th>
                <th rowspan="2" class="align-middle text-center" style="min-width:72px;">Tgl Submit</th>
                <th rowspan="2" class="align-middle" style="min-width:200px;">Judul</th>
                <th rowspan="2" class="align-middle" style="min-width:120px;">Penulis</th>
                <th colspan="4" class="text-center bg-primary">④ Reviewer 1</th>
                <th colspan="4" class="text-center bg-primary">⑤ Reviewer 2</th>
            </tr>
            <tr>
                {{-- Reviewer 1 --}}
                <th class="bg-primary">Petugas</th>
                <th class="bg-primary">User / Pass</th>
                <th class="bg-primary">Catatan</th>
                <th class="bg-primary text-center">✓</th>
                {{-- Reviewer 2 --}}
                <th class="bg-primary">Petugas</th>
                <th class="bg-primary">User / Pass</th>
                <th class="bg-primary">Catatan</th>
                <th class="bg-primary text-center">✓</th>
            </tr>
        </thead>
        <tbody>
            @forelse($submissions as $s)
            @php
                $rowClass = match(true) {
                    $s->status === 'PUBLISHED'                     => 'row-published',
                    $s->status === 'REJECTED'                      => 'row-rejected',
                    str_contains($s->status, '_SUBMITTED')         => 'row-pending',
                    str_contains($s->status, 'REVIEWER')           => 'row-progress',
                    default                                        => '',
                };

                $statusLabels = [
                    'SUBMITTED'               => ['Submitted',        'secondary'],
                    'SUBMIT_VALIDATED'        => ['Submit Valid',     'info'],
                    'EDITOR1_SUBMITTED'       => ['Editor1 Submit',   'warning'],
                    'EDITOR1_VALIDATED'       => ['Editor1 Valid',    'primary'],
                    'AUTHOR1_SUBMITTED'       => ['Author1 Submit',   'warning'],
                    'AUTHOR1_VALIDATED'       => ['Author1 Valid',    'primary'],
                    'EDITOR2_SUBMITTED'       => ['Editor2 Submit',   'warning'],
                    'EDITOR2_VALIDATED'       => ['Editor2 Valid',    'primary'],
                    'REVIEWER1_SUBMITTED'     => ['Rev1 Submit',      'warning'],
                    'REVIEWER1_VALIDATED'     => ['Rev1 Valid',       'primary'],
                    'REVIEWER2_SUBMITTED'     => ['Rev2 Submit',      'warning'],
                    'REVIEWER2_VALIDATED'     => ['Rev2 Valid',       'primary'],
                    'EDITOR3_SUBMITTED'       => ['Editor3 Submit',   'warning'],
                    'EDITOR3_VALIDATED'       => ['Editor3 Valid',    'primary'],
                    'AUTHOR2_SUBMITTED'       => ['Author2 Submit',   'warning'],
                    'AUTHOR2_VALIDATED'       => ['Author2 Valid',    'primary'],
                    'PRODUCTION_SUBMITTED'    => ['Prod Submit',      'warning'],
                    'PRODUCTION_VALIDATED'    => ['Prod Valid',       'success'],
                    'PUBLISHED'               => ['Published',        'success'],
                    'REJECTED'                => ['Rejected',         'danger'],
                ];
                [$statusLabel, $statusColor] = $statusLabels[$s->status] ?? [str_replace('_',' ',$s->status), 'secondary'];
            @endphp
            <tr class="{{ $rowClass }}">
                {{-- Kode --}}
                <td class="sticky-first">
                    <a href="{{ route('admin.submissions.show', $s) }}" class="fw-semibold text-decoration-none small">
                        {{ $s->kode_submit }}
                    </a>
                    <div class="text-muted" style="font-size:0.62rem;">
                        {{ Str::limit($s->journalSlot?->journalMaster?->nama_jurnal ?? '—', 22) }}
                    </div>
                </td>

                {{-- Status --}}
                <td class="text-center">
                    <span class="status-badge bg-{{ $statusColor }} text-white">{{ $statusLabel }}</span>
                </td>

                {{-- Tgl Submit --}}
                <td class="text-center text-muted" style="font-size:0.65rem;">
                    {{ $s->tanggal_submit ? \Carbon\Carbon::parse($s->tanggal_submit)->format('d/m/y') : '—' }}
                </td>

                {{-- Judul --}}
                <td style="max-width:240px;white-space:normal;line-height:1.3;">
                    <span style="font-size:0.7rem;">{{ Str::limit($s->judul_artikel, 80) }}</span>
                </td>

                {{-- Penulis --}}
                <td>
                    <div style="font-size:0.68rem;">{{ Str::limit($s->nama_penulis ?? '—', 25) }}</div>
                </td>

                {{-- Reviewer 1 --}}
                <td>
                    <select class="inline-assign-select lazy-select {{ $s->petugas_reviewer1_id ? 'has-value' : '' }}"
                            data-submission="{{ $s->id }}"
                            data-type="reviewer1"
                            data-model="pic"
                            data-selected="{{ $s->petugas_reviewer1_id }}"
                            onchange="quickAssign(this)">
                        <option value="">-- Pilih --</option>
                        @if($s->petugas_reviewer1_id)
                            <option value="{{ $s->petugas_reviewer1_id }}" selected>
                                {{ $pics->firstWhere('id', $s->petugas_reviewer1_id)?->name }}
                            </option>
                        @endif
                    </select>
                </td>
                <td>
                    <div class="credential-group">
                        <input type="text" class="inline-credential-input {{ $s->username_reviewer1 ? 'has-value' : '' }}"
                               value="{{ $s->username_reviewer1 }}" placeholder="user"
                               data-submission="{{ $s->id }}" data-field="username_reviewer1"
                               onchange="quickUpdateCredential(this)">
                        <span>/</span>
                        <input type="text" class="inline-credential-input {{ $s->password_reviewer1 ? 'has-value' : '' }}"
                               value="{{ $s->password_reviewer1 }}" placeholder="pass"
                               data-submission="{{ $s->id }}" data-field="password_reviewer1"
                               onchange="quickUpdateCredential(this)">
                    </div>
                </td>
                <td>
                    <input type="text" class="inline-credential-input {{ $s->catatan_reviewer1 ? 'has-value' : '' }}"
                           value="{{ $s->catatan_reviewer1 }}" placeholder="catatan..."
                           style="min-width:80px;"
                           data-submission="{{ $s->id }}" data-field="catatan_reviewer1"
                           onchange="quickUpdateCredential(this)">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-link p-0 border-0"
                            onclick="quickToggleValid(this)"
                            data-submission="{{ $s->id }}"
                            data-field="reviewer1_valid"
                            data-valid="{{ $s->reviewer1_valid ? '1' : '0' }}">
                        {!! $s->reviewer1_valid
                            ? '<i class="bi bi-check-circle-fill text-success"></i>'
                            : '<i class="bi bi-circle text-muted"></i>' !!}
                    </button>
                </td>

                {{-- Reviewer 2 --}}
                <td>
                    <select class="inline-assign-select lazy-select {{ $s->petugas_reviewer2_id ? 'has-value' : '' }}"
                            data-submission="{{ $s->id }}"
                            data-type="reviewer2"
                            data-model="pic"
                            data-selected="{{ $s->petugas_reviewer2_id }}"
                            onchange="quickAssign(this)">
                        <option value="">-- Pilih --</option>
                        @if($s->petugas_reviewer2_id)
                            <option value="{{ $s->petugas_reviewer2_id }}" selected>
                                {{ $pics->firstWhere('id', $s->petugas_reviewer2_id)?->name }}
                            </option>
                        @endif
                    </select>
                </td>
                <td>
                    <div class="credential-group">
                        <input type="text" class="inline-credential-input {{ $s->username_reviewer2 ? 'has-value' : '' }}"
                               value="{{ $s->username_reviewer2 }}" placeholder="user"
                               data-submission="{{ $s->id }}" data-field="username_reviewer2"
                               onchange="quickUpdateCredential(this)">
                        <span>/</span>
                        <input type="text" class="inline-credential-input {{ $s->password_reviewer2 ? 'has-value' : '' }}"
                               value="{{ $s->password_reviewer2 }}" placeholder="pass"
                               data-submission="{{ $s->id }}" data-field="password_reviewer2"
                               onchange="quickUpdateCredential(this)">
                    </div>
                </td>
                <td>
                    <input type="text" class="inline-credential-input {{ $s->catatan_reviewer2 ? 'has-value' : '' }}"
                           value="{{ $s->catatan_reviewer2 }}" placeholder="catatan..."
                           style="min-width:80px;"
                           data-submission="{{ $s->id }}" data-field="catatan_reviewer2"
                           onchange="quickUpdateCredential(this)">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-link p-0 border-0"
                            onclick="quickToggleValid(this)"
                            data-submission="{{ $s->id }}"
                            data-field="reviewer2_valid"
                            data-valid="{{ $s->reviewer2_valid ? '1' : '0' }}">
                        {!! $s->reviewer2_valid
                            ? '<i class="bi bi-check-circle-fill text-success"></i>'
                            : '<i class="bi bi-circle text-muted"></i>' !!}
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    Tidak ada submission ditemukan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($submissions->hasPages())
<div class="d-flex justify-content-center mt-3">
    {{ $submissions->links() }}
</div>
@endif

@endsection

@section('scripts')
<script>
const listPics = @json($pics->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));

document.addEventListener('focusin', function(e) {
    if (e.target && e.target.classList.contains('lazy-select')) {
        const sel = e.target;
        if (sel.dataset.loaded) return;
        const selectedVal = sel.dataset.selected;
        listPics.forEach(item => {
            if (String(item.id) !== String(selectedVal)) {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.name;
                sel.appendChild(opt);
            }
        });
        sel.dataset.loaded = 'true';
    }
});

function quickAssign(selectEl) {
    selectEl.classList.add('saving');
    fetch('{{ route("admin.submissions.quick-assign") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({
            submission_id: selectEl.dataset.submission,
            assignment_type: selectEl.dataset.type,
            petugas_id: selectEl.value || null
        })
    })
    .then(r => r.json())
    .then(data => {
        selectEl.classList.remove('saving');
        if (data.success) {
            selectEl.value ? selectEl.classList.add('has-value') : selectEl.classList.remove('has-value');
            selectEl.style.boxShadow = '0 0 0 2px rgba(25,135,84,.5)';
            setTimeout(() => selectEl.style.boxShadow = '', 1000);
        } else { alert('Gagal: ' + (data.message || 'Terjadi kesalahan')); location.reload(); }
    })
    .catch(() => { selectEl.classList.remove('saving'); location.reload(); });
}

function quickUpdateCredential(inputEl) {
    inputEl.classList.add('saving');
    fetch('{{ route("admin.submissions.quick-update-credential") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ submission_id: inputEl.dataset.submission, field: inputEl.dataset.field, value: inputEl.value.trim() })
    })
    .then(r => r.json())
    .then(data => {
        inputEl.classList.remove('saving');
        if (data.success) {
            inputEl.value ? inputEl.classList.add('has-value') : inputEl.classList.remove('has-value');
            inputEl.style.boxShadow = '0 0 0 2px rgba(25,135,84,.5)';
            setTimeout(() => inputEl.style.boxShadow = '', 1000);
        } else { alert('Gagal: ' + (data.message || 'Terjadi kesalahan')); }
    })
    .catch(() => { inputEl.classList.remove('saving'); });
}

function quickToggleValid(btn) {
    btn.disabled = true;
    fetch('{{ route("admin.submissions.toggle-valid-field") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ submission_id: btn.dataset.submission, field: btn.dataset.field })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            const newValid = btn.dataset.valid !== '1';
            btn.dataset.valid = newValid ? '1' : '0';
            btn.innerHTML = newValid
                ? '<i class="bi bi-check-circle-fill text-success"></i>'
                : '<i class="bi bi-circle text-muted"></i>';
        } else { alert('Gagal: ' + (data.message || 'Terjadi kesalahan')); }
    })
    .catch(() => { btn.disabled = false; });
}
</script>
@endsection
