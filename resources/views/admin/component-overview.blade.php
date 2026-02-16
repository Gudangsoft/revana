@extends('layouts.app')

@section('title', 'Component Editor - Admin')
@section('page-title', 'Visual Component Editor')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-palette"></i> Visual Component Editor</h4>
        <p class="text-muted mb-0">Ubah tampilan komponen langsung dari sini. Perubahan berlaku di semua halaman Marketing & PIC.</p>
    </div>
    <div>
        <form action="{{ route('admin.component-overview.reset') }}" method="POST" class="d-inline" 
              onsubmit="return confirm('Reset semua pengaturan ke default?')">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-counterclockwise"></i> Reset Default
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.component-overview.save') }}" method="POST">
@csrf

<!-- 1. STATUS BADGE SETTINGS -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-tag-fill"></i> Pengaturan Badge Status</h5>
        <span class="badge bg-light text-primary">Berlaku di: Dashboard, Submissions, Monitoring, Detail Slot</span>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Atur warna dan label untuk setiap status submission. Preview langsung terlihat di kolom kanan.</p>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Status</th>
                        <th>Warna Badge</th>
                        <th>Label Teks</th>
                        <th class="text-center">Preview</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statuses as $status)
                    <tr>
                        <td><code>{{ $status }}</code></td>
                        <td>
                            <select name="badge_color_{{ $status }}" class="form-select form-select-sm" 
                                    onchange="updateBadgePreview(this, '{{ $status }}')">
                                @foreach($colorOptions as $value => $label)
                                    <option value="{{ $value }}" {{ ($settings['badge_color_'.$status] ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" name="badge_label_{{ $status }}" class="form-control form-control-sm"
                                   value="{{ $settings['badge_label_'.$status] ?? $status }}"
                                   onkeyup="updateBadgeLabelPreview(this, '{{ $status }}')">
                        </td>
                        <td class="text-center">
                            <span id="badge-preview-{{ $status }}" class="badge {{ $settings['badge_color_'.$status] ?? 'bg-secondary' }}">
                                {{ $settings['badge_label_'.$status] ?? $status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 2. PROGRESS BAR SETTINGS -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-bar-chart-steps"></i> Pengaturan Progress Bar</h5>
        <span class="badge bg-light text-success">Berlaku di: Dashboard, Submissions, Monitoring</span>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-4">
                <label class="form-label fw-bold">Tinggi Progress Bar (px)</label>
                <input type="range" name="progress_height" class="form-range" min="4" max="24" step="2" 
                       value="{{ $settings['progress_height'] ?? 8 }}" id="progressHeightRange"
                       oninput="updateProgressPreview()">
                <div class="d-flex justify-content-between small text-muted">
                    <span>4px</span>
                    <span id="progressHeightValue">{{ $settings['progress_height'] ?? 8 }}px</span>
                    <span>24px</span>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Tampilkan Persentase</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="progress_show_text" value="1" 
                           id="progressShowText" {{ ($settings['progress_show_text'] ?? '1') == '1' ? 'checked' : '' }}
                           onchange="updateProgressPreview()">
                    <label class="form-check-label" for="progressShowText">Tampilkan teks persentase</label>
                </div>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold">Preview</label>
                <div id="progressPreviewContainer">
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" id="progressPreviewBar" style="height: {{ $settings['progress_height'] ?? 8 }}px;">
                            <div class="progress-bar bg-info" style="width: 60%"></div>
                        </div>
                        <small class="text-muted" id="progressPreviewText">60%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. TRACKING TABLE SETTINGS -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-table"></i> Pengaturan Tracking Table</h5>
        <span class="badge bg-light text-info">Berlaku di: Detail Submission Marketing</span>
    </div>
    <div class="card-body">
        <!-- Global toggle -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="tracking_show_credentials" value="1" 
                           id="trackingShowCreds" {{ ($settings['tracking_show_credentials'] ?? '1') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="trackingShowCreds">
                        <i class="bi bi-key"></i> Tampilkan Kolom Credential
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small">Badge Valid</label>
                <select name="tracking_valid_color" class="form-select form-select-sm">
                    @foreach($colorOptions as $value => $label)
                        <option value="{{ $value }}" {{ ($settings['tracking_valid_color'] ?? 'bg-success') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small">Badge In Progress</label>
                <select name="tracking_progress_color" class="form-select form-select-sm">
                    @foreach($colorOptions as $value => $label)
                        <option value="{{ $value }}" {{ ($settings['tracking_progress_color'] ?? 'bg-warning') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small">Badge Pending</label>
                <select name="tracking_pending_color" class="form-select form-select-sm">
                    @foreach($colorOptions as $value => $label)
                        <option value="{{ $value }}" {{ ($settings['tracking_pending_color'] ?? 'bg-secondary') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Per-step settings -->
        <h6 class="mb-3">Pengaturan Per Tahap</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tahap</th>
                        <th class="text-center" style="width: 80px;">Tampilkan</th>
                        <th>Warna Baris</th>
                        <th class="text-center">Preview Baris</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trackingSteps as $key => $label)
                    <tr>
                        <td><strong>{{ $label }}</strong></td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" name="tracking_show_{{ $key }}" value="1" 
                                       {{ ($settings['tracking_show_'.$key] ?? '1') == '1' ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td>
                            <select name="tracking_row_{{ $key }}" class="form-select form-select-sm"
                                    onchange="updateRowPreview(this, '{{ $key }}')">
                                @foreach($rowColorOptions as $value => $rlabel)
                                    <option value="{{ $value }}" {{ ($settings['tracking_row_'.$key] ?? '') == $value ? 'selected' : '' }}>
                                        {{ $rlabel }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-center">
                            <div id="row-preview-{{ $key }}" class="px-3 py-1 rounded {{ $settings['tracking_row_'.$key] ?? '' }}" style="display: inline-block; min-width: 100px;">
                                {{ $label }}
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SAVE BUTTON -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div class="text-muted">
            <i class="bi bi-info-circle"></i> Perubahan akan langsung berlaku di semua halaman Marketing dan PIC setelah disimpan.
        </div>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-save"></i> Simpan Semua Pengaturan
        </button>
    </div>
</div>

</form>

<!-- ============================================== -->
<!-- MARKETING MENU & FUNCTION MAPPING              -->
<!-- ============================================== -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
        <h5 class="mb-0"><i class="bi bi-megaphone"></i> Menu & Fungsi Marketing</h5>
        <span class="badge bg-light text-success">{{ collect($marketingMenus)->sum(fn($g) => count($g['items'])) }} fungsi</span>
    </div>
    <div class="card-body p-0">
        @foreach($marketingMenus as $group)
        <div class="px-3 py-2 bg-light border-bottom">
            <strong class="text-muted small text-uppercase"><i class="bi bi-folder2"></i> {{ $group['group'] }}</strong>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead>
                    <tr class="small text-muted">
                        <th style="width: 250px;">Menu</th>
                        <th style="width: 80px;">Method</th>
                        <th>Deskripsi</th>
                        <th style="width: 200px;">Route</th>
                        <th style="width: 180px;">Komponen UI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['items'] as $item)
                    <tr>
                        <td>
                            <i class="bi {{ $item['icon'] }} text-success me-1"></i>
                            <strong>{{ $item['name'] }}</strong>
                        </td>
                        <td>
                            @foreach(explode('/', $item['type']) as $method)
                                <span class="badge {{ $method === 'GET' ? 'bg-primary' : ($method === 'POST' ? 'bg-success' : ($method === 'PUT' ? 'bg-warning text-dark' : 'bg-danger')) }} small">{{ $method }}</span>
                            @endforeach
                        </td>
                        <td><small class="text-muted">{{ $item['description'] }}</small></td>
                        <td><code class="small">{{ $item['route'] }}</code></td>
                        <td>
                            @forelse($item['components'] as $comp)
                                <span class="badge bg-info me-1 small">{{ $comp }}</span>
                            @empty
                                <span class="text-muted small">-</span>
                            @endforelse
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
</div>

<!-- ============================================== -->
<!-- PIC MENU & FUNCTION MAPPING                    -->
<!-- ============================================== -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #667eea, #764ba2);">
        <h5 class="mb-0"><i class="bi bi-person-badge"></i> Menu & Fungsi PIC</h5>
        <span class="badge bg-light text-primary">{{ collect($picMenus)->sum(fn($g) => count($g['items'])) }} fungsi</span>
    </div>
    <div class="card-body p-0">
        @foreach($picMenus as $group)
        <div class="px-3 py-2 bg-light border-bottom">
            <strong class="text-muted small text-uppercase"><i class="bi bi-folder2"></i> {{ $group['group'] }}</strong>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead>
                    <tr class="small text-muted">
                        <th style="width: 250px;">Menu</th>
                        <th style="width: 120px;">Method</th>
                        <th>Deskripsi</th>
                        <th style="width: 200px;">Route</th>
                        <th style="width: 180px;">Komponen UI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['items'] as $item)
                    <tr>
                        <td>
                            <i class="bi {{ $item['icon'] }} text-primary me-1"></i>
                            <strong>{{ $item['name'] }}</strong>
                        </td>
                        <td>
                            @foreach(explode('/', $item['type']) as $method)
                                <span class="badge {{ $method === 'GET' ? 'bg-primary' : ($method === 'POST' ? 'bg-success' : ($method === 'PUT' ? 'bg-warning text-dark' : 'bg-danger')) }} small">{{ $method }}</span>
                            @endforeach
                        </td>
                        <td><small class="text-muted">{{ $item['description'] }}</small></td>
                        <td><code class="small">{{ $item['route'] }}</code></td>
                        <td>
                            @forelse($item['components'] as $comp)
                                <span class="badge bg-info me-1 small">{{ $comp }}</span>
                            @empty
                                <span class="text-muted small">-</span>
                            @endforelse
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
</div>

<!-- PERBEDAAN AKSES -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="bi bi-arrow-left-right"></i> Perbandingan Akses Marketing vs PIC</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fitur</th>
                        <th class="text-center" style="background: #e8f5e9;">Marketing</th>
                        <th class="text-center" style="background: #ede7f6;">PIC</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Kelola Jurnal (CRUD)</td>
                        <td class="text-center" style="background: #fce4ec;"><i class="bi bi-x-circle text-danger"></i> Read Only</td>
                        <td class="text-center" style="background: #e8f5e9;"><i class="bi bi-check-circle text-success"></i> Full CRUD</td>
                    </tr>
                    <tr>
                        <td>Kelola Slot Jurnal (CRUD)</td>
                        <td class="text-center" style="background: #fce4ec;"><i class="bi bi-x-circle text-danger"></i> Read Only</td>
                        <td class="text-center" style="background: #e8f5e9;"><i class="bi bi-check-circle text-success"></i> Full CRUD</td>
                    </tr>
                    <tr>
                        <td>Buat Submission</td>
                        <td class="text-center" style="background: #e8f5e9;"><i class="bi bi-check-circle text-success"></i> Ya</td>
                        <td class="text-center" style="background: #e8f5e9;"><i class="bi bi-check-circle text-success"></i> Ya</td>
                    </tr>
                    <tr>
                        <td>Proses/Kerjakan Submission</td>
                        <td class="text-center" style="background: #fce4ec;"><i class="bi bi-x-circle text-danger"></i> Tidak</td>
                        <td class="text-center" style="background: #e8f5e9;"><i class="bi bi-check-circle text-success"></i> Ya</td>
                    </tr>
                    <tr>
                        <td>Validasi Tahap Review</td>
                        <td class="text-center" style="background: #fce4ec;"><i class="bi bi-x-circle text-danger"></i> Tidak</td>
                        <td class="text-center" style="background: #e8f5e9;"><i class="bi bi-check-circle text-success"></i> Ya</td>
                    </tr>
                    <tr>
                        <td>Assign Petugas</td>
                        <td class="text-center" style="background: #fce4ec;"><i class="bi bi-x-circle text-danger"></i> Tidak</td>
                        <td class="text-center" style="background: #e8f5e9;"><i class="bi bi-check-circle text-success"></i> Ya</td>
                    </tr>
                    <tr>
                        <td>Update Credential</td>
                        <td class="text-center" style="background: #fce4ec;"><i class="bi bi-x-circle text-danger"></i> Tidak</td>
                        <td class="text-center" style="background: #e8f5e9;"><i class="bi bi-check-circle text-success"></i> Ya</td>
                    </tr>
                    <tr>
                        <td>Fasttrack (Full)</td>
                        <td class="text-center" style="background: #fff8e1;"><i class="bi bi-dash-circle text-warning"></i> Create + View</td>
                        <td class="text-center" style="background: #e8f5e9;"><i class="bi bi-check-circle text-success"></i> Full CRUD + Assignment</td>
                    </tr>
                    <tr>
                        <td>My Tasks</td>
                        <td class="text-center" style="background: #fce4ec;"><i class="bi bi-x-circle text-danger"></i> Tidak</td>
                        <td class="text-center" style="background: #e8f5e9;"><i class="bi bi-check-circle text-success"></i> Ya</td>
                    </tr>
                    <tr>
                        <td>Daftar Reviewer</td>
                        <td class="text-center" style="background: #fce4ec;"><i class="bi bi-x-circle text-danger"></i> Tidak</td>
                        <td class="text-center" style="background: #e8f5e9;"><i class="bi bi-check-circle text-success"></i> Ya + Login As</td>
                    </tr>
                    <tr>
                        <td>Catatan Marketing</td>
                        <td class="text-center" style="background: #e8f5e9;"><i class="bi bi-check-circle text-success"></i> Ya</td>
                        <td class="text-center" style="background: #fce4ec;"><i class="bi bi-x-circle text-danger"></i> Tidak</td>
                    </tr>
                    <tr>
                        <td>Point & Laporan</td>
                        <td class="text-center" style="background: #e8f5e9;"><i class="bi bi-check-circle text-success"></i> Ya</td>
                        <td class="text-center" style="background: #e8f5e9;"><i class="bi bi-check-circle text-success"></i> Ya</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- LIVE PREVIEW -->
@if($sampleSubmission)
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="bi bi-eye"></i> Live Preview (Data Asli)</h5>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Preview menggunakan data submission: 
            <strong>{{ $sampleSubmission->kode_submit }}</strong> - {{ Str::limit($sampleSubmission->judul_artikel, 50) }}
        </p>
        
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border h-100">
                    <div class="card-header bg-light py-2">
                        <small class="fw-bold">Status Badge</small>
                    </div>
                    <div class="card-body text-center">
                        <x-submission-status :submission="$sampleSubmission" />
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card border h-100">
                    <div class="card-header bg-light py-2">
                        <small class="fw-bold">Progress Bar</small>
                    </div>
                    <div class="card-body">
                        <x-submission-progress :submission="$sampleSubmission" />
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border h-100">
                    <div class="card-header bg-light py-2">
                        <small class="fw-bold">Slot Link</small>
                    </div>
                    <div class="card-body">
                        @if($sampleSubmission->journalSlot)
                            Marketing: <x-slot-link :journal-slot="$sampleSubmission->journalSlot" guard="marketing" /><br>
                            Admin: <x-slot-link :journal-slot="$sampleSubmission->journalSlot" guard="admin" />
                        @else
                            <span class="text-muted small">No slot</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tracking Table Preview -->
        <x-tracking-table :submission="$sampleSubmission" />
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
function updateBadgePreview(select, status) {
    const badge = document.getElementById('badge-preview-' + status);
    // Remove all bg- classes
    badge.className = badge.className.replace(/bg-\w+/g, '');
    badge.classList.add('badge', select.value);
}

function updateBadgeLabelPreview(input, status) {
    const badge = document.getElementById('badge-preview-' + status);
    badge.textContent = input.value;
}

function updateProgressPreview() {
    const height = document.getElementById('progressHeightRange').value;
    const showText = document.getElementById('progressShowText').checked;
    
    document.getElementById('progressHeightValue').textContent = height + 'px';
    document.getElementById('progressPreviewBar').style.height = height + 'px';
    document.getElementById('progressPreviewText').style.display = showText ? '' : 'none';
}

function updateRowPreview(select, key) {
    const preview = document.getElementById('row-preview-' + key);
    // Remove all table- classes
    preview.className = preview.className.replace(/table-\w+/g, '');
    preview.classList.add('px-3', 'py-1', 'rounded');
    if (select.value) {
        preview.classList.add(select.value);
    }
}
</script>
@endsection
