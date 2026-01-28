@extends('pic.layouts.app')

@section('title', 'Monitoring Fasttrack')
@section('page-title', 'Monitoring Fasttrack')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<style>
/* Override content width for this page */
.content {
    max-width: 100vw;
    overflow-x: hidden;
}

/* Sticky Table Styles for Monitoring */
.monitoring-scroll-wrapper {
    overflow-x: auto;
    overflow-y: auto;
    max-height: calc(100vh - 400px);
    min-height: 300px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    scrollbar-width: thin;
    scrollbar-color: #6c757d #dee2e6;
}

.monitoring-scroll-wrapper::-webkit-scrollbar {
    height: 12px;
    width: 12px;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 6px;
    border: 2px solid #f1f1f1;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.monitoring-scroll-wrapper::-webkit-scrollbar-corner {
    background: #dee2e6;
}

/* Summary cards */
.summary-cards {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.summary-card {
    flex: 0 0 auto;
    min-width: 120px;
    max-width: 180px;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    padding: 12px 15px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.summary-card h6 {
    font-size: 0.7rem;
    color: #6c757d;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.summary-card .value {
    font-size: 1.75rem;
    font-weight: bold;
    color: #212529;
}

.summary-card.my-tasks {
    border-left: 4px solid #ffc107;
}

.summary-card.all-tasks {
    border-left: 4px solid #0d6efd;
}

/* Scroll controls */
.scroll-controls {
    margin-bottom: 10px;
    padding: 8px 12px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 6px;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    position: relative;
    z-index: 20;
}

.scroll-nav-btn {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
    z-index: 21;
}

.scroll-nav-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #0b5ed7, #0a58ca);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

.scroll-nav-btn:disabled {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    cursor: not-allowed;
    opacity: 0.5;
}

.scroll-position-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    max-width: 300px;
}

.scroll-position-bar {
    width: 100px;
    height: 4px;
    background-color: #e9ecef;
    border-radius: 2px;
    position: relative;
    overflow: hidden;
}

.scroll-position-fill {
    height: 100%;
    background: linear-gradient(90deg, #0d6efd, #6610f2);
    border-radius: 2px;
    transition: width 0.1s ease;
}

/* Jump button styling */
.jump-btn {
    font-size: 0.75rem;
    padding: 4px 8px;
    margin: 1px;
}

.table-monitoring {
    border-collapse: collapse;
    font-size: 0.8rem;
    margin: 0;
    border-spacing: 0;
}

.table-monitoring thead th {
    position: sticky;
    top: 0;
    z-index: 20;
    font-size: 0.7rem;
    padding: 4px;
    border: 1px solid #dee2e6;
    white-space: nowrap;
    background-color: #212529 !important;
    color: #fff !important;
    line-height: 1;
    vertical-align: middle;
    height: 32px;
}

.table-monitoring thead tr:nth-child(2) th {
    position: sticky;
    top: 32px;
    z-index: 20;
    line-height: 1;
    vertical-align: middle;
    height: 32px;
}

.table-monitoring thead th.bg-info {
    background-color: #0dcaf0 !important;
    color: #000 !important;
    z-index: 20 !important;
    position: sticky !important;
}

.table-monitoring thead th.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
    z-index: 20 !important;
    position: sticky !important;
}

.table-monitoring thead th.bg-primary {
    background-color: #6f42c1 !important;
    color: #fff !important;
    z-index: 20 !important;
    position: sticky !important;
}

.table-monitoring thead th.bg-success {
    background-color: #198754 !important;
    color: #fff !important;
    z-index: 20 !important;
    position: sticky !important;
}

.table-monitoring thead th.bg-dark {
    background-color: #212529 !important;
    color: #fff !important;
    z-index: 20 !important;
    position: sticky !important;
}

/* Sticky columns */
.table-monitoring th.sticky-first,
.table-monitoring td.sticky-first {
    position: sticky;
    left: 0;
    z-index: 2;
    background: #fff;
    min-width: 120px;
    box-shadow: 3px 0 6px -3px rgba(0,0,0,0.15);
}

.table-monitoring thead th.sticky-first {
    z-index: 5;
    background: #212529 !important;
}

.table-monitoring th.sticky-second,
.table-monitoring td.sticky-second {
    position: sticky;
    left: 120px;
    z-index: 2;
    background: #fff;
    min-width: 100px;
    box-shadow: 3px 0 6px -3px rgba(0,0,0,0.15);
}

.table-monitoring thead th.sticky-second {
    z-index: 5;
    background: #212529 !important;
}
    font-size: 11px;
    line-height: 1.3;
    color: #495057;
}

.table-monitoring .no-wrap {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.table-monitoring .btn {
    font-size: 11px;
    padding: 3px 8px;
    margin: 1px;
    border-radius: 3px;
    white-space: nowrap;
}

.table-monitoring select {
    font-size: 11px;
    padding: 4px 6px;
    border-radius: 4px;
    border: 1px solid #ced4da;
    background-color: white;
    min-width: 90px;
    max-width: 140px;
}

.table-monitoring input[type="checkbox"] {
    transform: scale(1.2);
}

/* Status indicators */
.status-validated {
    color: #28a745;
    font-weight: 600;
}

.status-pending {
    color: #dc3545;
    font-weight: 600;
}

.status-partial {
    color: #ffc107;
    font-weight: 600;
}

.validation-checkbox:checked + label {
    color: #28a745;
    font-weight: bold;
}

.validation-checkbox:not(:checked) + label {
    color: #dc3545;
}

/* Better spacing for action columns */
.action-column {
    min-width: 120px;
    text-align: center;
}

.assignment-column {
    min-width: 100px;
}

/* Loading indicator */
.loading-assignments {
    opacity: 0.5;
    pointer-events: none;
}

.pic-assignment-select {
    min-width: 120px;
    max-width: 150px;
}

.navigation-buttons {
    margin: 20px 0;
}

.navigation-buttons .btn {
    font-size: 14px;
    margin-right: 10px;
    padding: 8px 16px;
}

.page-header {
    margin-bottom: 20px;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 15px;
}

.page-header h4 {
    color: #495057;
    font-weight: 600;
    margin: 0;
}

.table-responsive {
    border: none;
}

.progress-bar-container {
    width: 100%;
    max-width: 200px;
}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <span><i class="bi bi-lightning-charge text-warning"></i> Monitoring Proses Submit Fasttrack</span>
                    <a href="{{ route('pic.submissions.monitoring') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-clipboard-data"></i> Lihat Normal
                    </a>
                </div>
                <div class="alert alert-warning mb-0 py-2 px-3" style="font-size: 0.875rem;">
                    <i class="bi bi-lightning-charge"></i> 
                    Halaman ini menampilkan data <strong>submissions fasttrack</strong> saja.
                </div>
            </div>
            <div class="card-body">
                <!-- Summary Cards -->
                <div class="summary-cards mb-3">
                    <div class="summary-card my-tasks">
                        <h6>Total Fasttrack</h6>
                        <div class="value">{{ $totalFasttrack }}</div>
                    </div>
                    <div class="summary-card all-tasks">
                        <h6>Bulan Ini</h6>
                        <div class="value">{{ $thisMonthFasttrack }}</div>
                    </div>
                </div>
                <!-- Filter Form -->
                <form action="{{ route('pic.fasttrack.monitoring') }}" method="GET" class="mb-3" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label for="tanggal_dari" class="form-label small mb-1">Tanggal Dari</label>
                            <input type="date" class="form-control form-control-sm" style="width: 150px;" id="tanggal_dari" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
                        </div>
                        <div class="col-auto">
                            <label for="tanggal_sampai" class="form-label small mb-1">Tanggal Sampai</label>
                            <input type="date" class="form-control form-control-sm" style="width: 150px;" id="tanggal_sampai" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
                        </div>
                        <div class="col-auto">
                            <label for="journal_id" class="form-label small mb-1">Jurnal</label>
                            <select class="form-select form-select-sm" style="width: 180px;" id="journal_id" name="journal_id">
                                <option value="">-- Semua --</option>
                                @foreach($journals as $journal)
                                    <option value="{{ $journal->id }}" {{ request('journal_id') == $journal->id ? 'selected' : '' }}>
                                        {{ Str::limit($journal->nama_jurnal, 20) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                                <a href="{{ route('pic.fasttrack.monitoring') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Important Info Alert -->
                <div class="alert alert-info d-flex align-items-start mb-3" style="font-size: 0.85rem;">
                    <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                    <div>
                        <strong>Info Penting:</strong>
                        <ul class="mb-0 mt-1" style="font-size: 0.8rem;">
                            <li><strong>Link Publish</strong> hanya bisa diedit jika validasi Production <strong>belum dicentang</strong>.</li>
                            <li>Jika hendak mengubah Link Publish, <strong>matikan validasi Production</strong> terlebih dahulu dengan klik tombol centang hijau.</li>
                            <li>Setelah selesai edit, centang kembali validasi Production untuk mengunci data.</li>
                        </ul>
                    </div>
                </div>

                <!-- Scroll Controls -->
                <div class="scroll-controls">
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" class="scroll-nav-btn" id="scrollStartBtn" title="Ke Awal">
                            <i class="bi bi-chevron-bar-left"></i>
                        </button>
                        <button type="button" class="scroll-nav-btn" id="scrollLeftBtn" title="Scroll Kiri">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <div class="scroll-position-indicator">
                            <div class="scroll-position-bar">
                                <div class="scroll-position-fill" id="scrollPositionFill" style="width: 0%"></div>
                            </div>
                            <small class="text-muted" id="scrollPositionText">0%</small>
                        </div>
                        <button type="button" class="scroll-nav-btn" id="scrollRightBtn" title="Scroll Kanan">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                        <button type="button" class="scroll-nav-btn" id="scrollEndBtn" title="Ke Akhir">
                            <i class="bi bi-chevron-bar-right"></i>
                        </button>
                    </div>
                    <div class="mt-2">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary jump-btn" data-target="Submit">Submit</button>
                            <button type="button" class="btn btn-outline-warning jump-btn" data-target="Editor1">Editor1</button>
                            <button type="button" class="btn btn-outline-info jump-btn" data-target="Author1">Author1</button>
                            <button type="button" class="btn btn-outline-warning jump-btn" data-target="Editor2">Editor2</button>
                            <button type="button" class="btn btn-outline-primary jump-btn" data-target="Reviewer1">Reviewer1</button>
                            <button type="button" class="btn btn-outline-primary jump-btn" data-target="Reviewer2">Reviewer2</button>
                            <button type="button" class="btn btn-outline-warning jump-btn" data-target="Editor3">Editor3</button>
                            <button type="button" class="btn btn-outline-info jump-btn" data-target="Author2">Author2</button>
                            <button type="button" class="btn btn-outline-success jump-btn" data-target="Production">Production</button>
                        </div>
                    </div>
                </div>

        <!-- Main Monitoring Table -->
        <div class="monitoring-scroll-wrapper">
            <table class="table table-monitoring table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="sticky-col">#</th>
                        <th class="sticky-col">Kode Submit</th>
                        <th class="sticky-col">Jurnal</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Vol/No</th>
                        <th>Halaman</th>
                        <th>Marketing</th>
                        <th>Link Publish</th>
                        <th>Submit Date</th>
                        <th>Validasi</th>
                        <th>Editor 1</th>
                        <th>Editor 2</th>
                        <th>Editor 3</th>
                        <th>Author 1</th>
                        <th>Author 2</th>
                        <th>Reviewer 1</th>
                        <th>Reviewer 2</th>
                        <th>Production</th>
                        <th>Upload Proof</th>
                        <th>Finalisasi</th>
                        <th class="action-column">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $index => $submission)
                    <tr>
                        <td class="sticky-col">{{ $index + 1 }}</td>
                        <td class="sticky-col">
                            <strong class="text-primary">{{ $submission->kode_submit ?? $submission->submission_code }}</strong>
                        </td>
                        <td class="sticky-col">
                            <div class="no-wrap" title="{{ $submission->journalSlot->journalMaster->nama_jurnal ?? 'N/A' }}">
                                {{ Str::limit($submission->journalSlot->journalMaster->nama_jurnal ?? 'N/A', 20) }}
                            </div>
                        </td>
                        <td>
                            <div class="no-wrap" title="{{ $submission->judul_artikel ?? $submission->title }}">
                                {{ Str::limit($submission->judul_artikel ?? $submission->title, 30) }}
                            </div>
                        </td>
                        <td>
                            <div class="no-wrap" title="{{ $submission->nama_penulis ?? $submission->authors }}">
                                {{ Str::limit($submission->nama_penulis ?? $submission->authors, 25) }}
                            </div>
                        </td>
                        <td>
                            @if($submission->volume_number && $submission->issue_number)
                                Vol.{{ $submission->volume_number }} No.{{ $submission->issue_number }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($submission->start_page && $submission->end_page)
                                {{ $submission->start_page }}-{{ $submission->end_page }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($submission->marketing)
                                <span class="badge badge-success">{{ $submission->marketing }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($submission->link_publish)
                                <a href="{{ $submission->link_publish }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $submission->tanggal_submit ? \Carbon\Carbon::parse($submission->tanggal_submit)->format('d/m/Y') : $submission->created_at->format('d/m/Y') }}</td>
                        
                        <!-- Validasi Column -->
                        <td>
                            <div class="custom-control custom-checkbox">
                                <input 
                                    type="checkbox" 
                                    class="custom-control-input validation-checkbox" 
                                    id="validation_{{ $submission->id }}"
                                    {{ $submission->is_validated ? 'checked' : '' }}
                                    onchange="toggleValidation({{ $submission->id }}, this.checked)"
                                >
                                <label class="custom-control-label" for="validation_{{ $submission->id }}">
                                    {{ $submission->is_validated ? 'Valid' : 'Pending' }}
                                </label>
                            </div>
                        </td>

                        <!-- Editor 1 -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'petugas_editor1_id', this.value)">
                                <option value="">Pilih Editor 1</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->petugas_editor1_id == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Editor 2 -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'petugas_editor2_id', this.value)">
                                <option value="">Pilih Editor 2</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->petugas_editor2_id == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Editor 3 -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'petugas_editor3_id', this.value)">
                                <option value="">Pilih Editor 3</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->petugas_editor3_id == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Author 1 -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'petugas_author1_id', this.value)">
                                <option value="">Pilih Author 1</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->petugas_author1_id == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Author 2 -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'petugas_author2_id', this.value)">
                                <option value="">Pilih Author 2</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->petugas_author2_id == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Reviewer 1 -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'petugas_reviewer1_id', this.value)">
                                <option value="">Pilih Reviewer 1</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->petugas_reviewer1_id == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Reviewer 2 -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'petugas_reviewer2_id', this.value)">
                                <option value="">Pilih Reviewer 2</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->petugas_reviewer2_id == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Production -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'petugas_production_id', this.value)">
                                <option value="">Pilih Production</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->petugas_production_id == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Upload Proof -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'petugas_upload_proof_id', this.value)">
                                <option value="">Pilih Upload</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->petugas_upload_proof_id == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Finalisasi -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'petugas_finalisasi_id', this.value)">
                                <option value="">Pilih Finalisasi</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->petugas_finalisasi_id == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Actions -->
                        <td class="action-column">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('pic.fasttrack.edit', $submission->id) }}" 
                                   class="btn btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('pic.fasttrack.show', $submission->id) }}" 
                                   class="btn btn-outline-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="22" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada data submit fasttrack</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
            </div>
        </div>
    </div>
</div>

<script>
// Scroll controls functionality
document.addEventListener('DOMContentLoaded', function() {
    const scrollContainer = document.querySelector('.monitoring-scroll-wrapper');
    const scrollLeftBtn = document.getElementById('scrollLeftBtn');
    const scrollRightBtn = document.getElementById('scrollRightBtn');
    const scrollStartBtn = document.getElementById('scrollStartBtn');
    const scrollEndBtn = document.getElementById('scrollEndBtn');
    const scrollPositionFill = document.getElementById('scrollPositionFill');
    const scrollPositionText = document.getElementById('scrollPositionText');
    const jumpBtns = document.querySelectorAll('.jump-btn');

    function updateScrollPosition() {
        if (scrollContainer.scrollWidth > scrollContainer.clientWidth) {
            const scrollPercentage = (scrollContainer.scrollLeft / (scrollContainer.scrollWidth - scrollContainer.clientWidth)) * 100;
            scrollPositionFill.style.width = scrollPercentage + '%';
            scrollPositionText.textContent = Math.round(scrollPercentage) + '%';
        }
    }

    scrollLeftBtn.addEventListener('click', () => {
        scrollContainer.scrollLeft -= 200;
    });

    scrollRightBtn.addEventListener('click', () => {
        scrollContainer.scrollLeft += 200;
    });

    scrollStartBtn.addEventListener('click', () => {
        scrollContainer.scrollLeft = 0;
    });

    scrollEndBtn.addEventListener('click', () => {
        scrollContainer.scrollLeft = scrollContainer.scrollWidth;
    });

    scrollContainer.addEventListener('scroll', updateScrollPosition);

    // Jump to specific sections
    jumpBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.dataset.target;
            const targetElement = document.querySelector(`th:contains('${target}')`);
            if (targetElement) {
                targetElement.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
            }
        });
    });

    // Initial position update
    updateScrollPosition();
});

// Toggle validation status
function toggleValidation(submissionId, field, button) {
    fetch(`{{ route('pic.fasttrack.toggle-validation') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            submission_id: submissionId,
            field: field
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button appearance
            if (data.is_valid) {
                button.className = 'validation-toggle btn btn-success btn-sm';
                button.innerHTML = '<i class="bi bi-check"></i>';
                button.title = 'Valid - Klik untuk batalkan';
            } else {
                button.className = 'validation-toggle btn btn-outline-secondary btn-sm';
                button.innerHTML = '<i class="bi bi-x"></i>';
                button.title = 'Belum Valid - Klik untuk validasi';
            }
            showAlert('success', data.message);
        } else {
            showAlert('danger', data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan sistem');
    });
}

// Update PIC assignment
function updatePicAssignment(submissionId, field, picId) {
    const selectElement = event.target;
    selectElement.disabled = true;
    selectElement.classList.add('saving');

    fetch(`{{ route('pic.fasttrack.update-assignment') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            submission_id: submissionId,
            field: field,
            pic_id: picId
        })
    })
    .then(response => response.json())
    .then(data => {
        selectElement.disabled = false;
        selectElement.classList.remove('saving');
        
        if (data.success) {
            selectElement.classList.add('success');
            showAlert('success', data.message);
            setTimeout(() => {
                selectElement.classList.remove('success');
            }, 2000);
        } else {
            showAlert('danger', data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        selectElement.disabled = false;
        selectElement.classList.remove('saving');
        showAlert('danger', 'Terjadi kesalahan sistem');
    });
}

// Show alert messages
function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        if (alerts.length > 0) {
            alerts[alerts.length - 1].remove();
        }
    }, 3000);
}
</script>
@endsection