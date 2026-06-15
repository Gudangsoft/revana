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
     MAIN FORM (update existing settings)
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
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted d-none d-md-block">Point diberikan saat tahap selesai divalidasi</small>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAddPic">
                    <i class="bi bi-plus-lg"></i> Tambah Task
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="picTable">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th width="48" class="text-center text-muted">#</th>
                        <th width="120">Step Key</th>
                        <th>Label Tugas <small class="text-muted fw-normal">(klik untuk edit)</small></th>
                        <th width="160" class="text-center">Point per Tugas</th>
                        <th width="80" class="text-center">Aktif</th>
                        <th width="48" class="text-center"></th>
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
                        <td class="text-center">
                            <button type="button" class="btn btn-link btn-sm text-danger p-0" title="Hapus task"
                                    onclick="confirmDelete({{ $setting->id }}, '{{ addslashes($setting->task_label) }}', '{{ route('admin.task-point-settings.destroy', $setting->id) }}')">
                                <i class="bi bi-trash"></i>
                            </button>
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
                        <td></td>
                    </tr>
                    @endif
                    @endforeach

                    {{-- Custom PIC tasks (not in default step order) --}}
                    @php
                        $customPicSettings = $picSettings->filter(fn($s) => !in_array($s->task_key, $picOrder));
                    @endphp
                    @foreach($customPicSettings as $setting)
                    <tr class="step-row {{ $setting->is_active ? '' : 'inactive-row' }}" data-step="{{ $setting->task_key }}">
                        <td class="text-center">
                            <span class="badge rounded-pill {{ $setting->is_active ? 'text-bg-info' : 'text-bg-secondary' }} step-num-badge">
                                <i class="bi bi-asterisk" style="font-size:.6rem;"></i>
                            </span>
                        </td>
                        <td>
                            <code class="text-info" style="font-size:.8rem;">{{ $setting->task_key }}</code>
                            <span class="badge text-bg-light border ms-1" style="font-size:.6rem;">custom</span>
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
                                       data-step="{{ $setting->task_key }}"
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
                                       data-step="{{ $setting->task_key }}"
                                       value="1"
                                       role="switch"
                                       {{ $setting->is_active ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-link btn-sm text-danger p-0" title="Hapus task"
                                    onclick="confirmDelete({{ $setting->id }}, '{{ addslashes($setting->task_label) }}', '{{ route('admin.task-point-settings.destroy', $setting->id) }}')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot style="background:#f8fafc;">
                    <tr>
                        <td colspan="3" class="text-end pe-3 fw-semibold text-muted">Total point PIC untuk alur lengkap:</td>
                        <td class="text-center">
                            <strong class="text-primary fs-5" id="picTableTotal">—</strong>
                            <small class="text-muted"> pt</small>
                        </td>
                        <td colspan="2"></td>
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
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted d-none d-md-block">Point diberikan saat submission baru masuk dengan kode Marketing</small>
                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalAddMarketing">
                    <i class="bi bi-plus-lg"></i> Tambah Task
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th width="120">Step Key</th>
                        <th>Label</th>
                        <th width="160" class="text-center">Point per Submission</th>
                        <th width="80" class="text-center">Aktif</th>
                        <th width="48" class="text-center"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($marketingSettings as $setting)
                    <tr>
                        <td>
                            <code class="text-success" style="font-size:.8rem;">{{ $setting->task_key }}</code>
                        </td>
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
                        <td class="text-center">
                            <button type="button" class="btn btn-link btn-sm text-danger p-0" title="Hapus task"
                                    onclick="confirmDelete({{ $setting->id }}, '{{ addslashes($setting->task_label) }}', '{{ route('admin.task-point-settings.destroy', $setting->id) }}')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3 fst-italic">
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
            <i class="bi bi-info-circle-fill me-1"></i>
            <strong>Simpan & Sync</strong> akan menyimpan pengaturan dan menghitung ulang total poin semua PIC dan Marketing secara otomatis.
            Untuk menyinkronkan data historis secara penuh, gunakan <strong>Sync Ulang Poin</strong> di:
            <a href="{{ route('admin.pic-points.index') }}" class="alert-link" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Laporan Poin PIC
            </a>
            &nbsp;|&nbsp;
            <a href="{{ route('admin.marketing-points.index') }}" class="alert-link" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Laporan Poin Marketing
            </a>
        </div>
        <button type="submit" class="btn btn-primary px-5 flex-shrink-0">
            <i class="bi bi-save me-1"></i> Simpan & Sync
        </button>
    </div>
</form>

{{-- ================================================================
     DELETE FORM (shared, action set dynamically by JS)
     ================================================================ --}}
<form id="deleteTaskForm" method="POST" action="">
    @csrf
    @method('DELETE')
</form>

{{-- ================================================================
     MODAL: Tambah Task PIC
     ================================================================ --}}
<div class="modal fade" id="modalAddPic" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.task-point-settings.store') }}" method="POST">
                @csrf
                <input type="hidden" name="user_type" value="pic">
                <div class="modal-header py-2" style="background:#e0e7ff; border-bottom:2px solid #818cf8;">
                    <h6 class="modal-title fw-bold mb-0">
                        <i class="bi bi-person-badge-fill text-primary me-1"></i> Tambah Task PIC Baru
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Task Key <span class="text-danger">*</span></label>
                        <input type="text" name="task_key" class="form-control form-control-sm" required
                               placeholder="contoh: proof_reading, reviewer3"
                               pattern="[a-z0-9_]+" title="Huruf kecil, angka, dan underscore saja">
                        <div class="form-text">Huruf kecil + underscore saja. Contoh: <code>proof_reading</code></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Label Tugas <span class="text-danger">*</span></label>
                        <input type="text" name="task_label" class="form-control form-control-sm" required
                               placeholder="contoh: Proof Reading" maxlength="100">
                    </div>
                    <div>
                        <label class="form-label fw-semibold small">Point <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm" style="max-width:160px;">
                            <input type="number" name="points" class="form-control" required
                                   value="1" min="0" step="0.01">
                            <span class="input-group-text">pt</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-plus-lg me-1"></i> Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
     MODAL: Tambah Task Marketing
     ================================================================ --}}
<div class="modal fade" id="modalAddMarketing" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.task-point-settings.store') }}" method="POST">
                @csrf
                <input type="hidden" name="user_type" value="marketing">
                <div class="modal-header py-2" style="background:#dcfce7; border-bottom:2px solid #4ade80;">
                    <h6 class="modal-title fw-bold mb-0">
                        <i class="bi bi-megaphone-fill text-success me-1"></i> Tambah Task Marketing Baru
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Task Key <span class="text-danger">*</span></label>
                        <input type="text" name="task_key" class="form-control form-control-sm" required
                               placeholder="contoh: referral, bonus_target"
                               pattern="[a-z0-9_]+" title="Huruf kecil, angka, dan underscore saja">
                        <div class="form-text">Huruf kecil + underscore saja. Contoh: <code>referral</code></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Label Tugas <span class="text-danger">*</span></label>
                        <input type="text" name="task_label" class="form-control form-control-sm" required
                               placeholder="contoh: Referral Marketing" maxlength="100">
                    </div>
                    <div>
                        <label class="form-label fw-semibold small">Point <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm" style="max-width:160px;">
                            <input type="number" name="points" class="form-control" required
                                   value="1" min="0" step="0.01">
                            <span class="input-group-text">pt</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4">
                        <i class="bi bi-plus-lg me-1"></i> Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
</style>
@endsection

@section('scripts')
<script>
function recalc() {
    let total = 0;
    document.querySelectorAll('.pic-point-input').forEach(input => {
        const row    = input.closest('tr');
        const toggle = row ? row.querySelector('.pic-active-toggle') : null;
        const active = toggle ? toggle.checked : true;
        if (active) total += parseFloat(input.value) || 0;
    });

    const fmtTotal = total % 1 === 0 ? total : total.toFixed(2);
    const el = document.getElementById('picTableTotal');
    if (el) el.textContent = fmtTotal;
}

function syncRowStyle(toggle) {
    const row = toggle.closest('tr');
    if (!row) return;
    row.classList.toggle('inactive-row', !toggle.checked);
    const badge = row.querySelector('.step-num-badge');
    if (badge) badge.className = 'badge rounded-pill step-num-badge ' + (toggle.checked ? 'text-bg-primary' : 'text-bg-secondary');
    recalc();
}

function confirmDelete(id, label, url) {
    if (!confirm('Hapus task "' + label + '"?\n\nData historis poin yang sudah tercatat tidak akan terhapus, tetapi task ini tidak akan muncul lagi di pengaturan.')) {
        return;
    }
    const form = document.getElementById('deleteTaskForm');
    form.action = url;
    form.submit();
}

document.querySelectorAll('.pic-point-input').forEach(el => el.addEventListener('input', recalc));
document.querySelectorAll('.pic-active-toggle').forEach(el => el.addEventListener('change', () => syncRowStyle(el)));

recalc();
</script>
@endsection
