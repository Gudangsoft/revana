@extends('pic.layouts.app')

@section('title', 'Data Submit Fasttrack')
@section('page-title', '')
@section('sidebar-class', '')

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

.table-monitoring {
    width: max-content;
    min-width: 100%;
    margin-bottom: 0;
    white-space: nowrap;
    font-size: 11px;
    background: white;
}

.table-monitoring th,
.table-monitoring td {
    padding: 6px 8px;
    vertical-align: middle;
    border: 1px solid #dee2e6;
    background: white;
    position: relative;
    word-wrap: break-word;
    min-width: 70px;
}

/* Sticky columns for better navigation */
.table-monitoring .sticky-col:first-child,
.table-monitoring .sticky-col:nth-child(2),
.table-monitoring .sticky-col:nth-child(3) {
    position: sticky;
    background: #f8f9fa;
    z-index: 10;
    border-right: 2px solid #007bff;
}

.table-monitoring .sticky-col:first-child {
    left: 0;
}

.table-monitoring .sticky-col:nth-child(2) {
    left: 70px;
}

.table-monitoring .sticky-col:nth-child(3) {
    left: 190px;
}

.table-monitoring th.sticky-col {
    background: #e9ecef;
    font-weight: 600;
    z-index: 11;
}

/* Responsive adjustments */
.table-monitoring th {
    font-weight: 600;
    background-color: #e9ecef;
    text-align: center;
    font-size: 10px;
    line-height: 1.2;
}

.table-monitoring .no-wrap {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
}

.table-monitoring .btn {
    font-size: 10px;
    padding: 2px 6px;
    margin: 1px;
    border-radius: 3px;
    white-space: nowrap;
}

.table-monitoring select {
    font-size: 10px;
    padding: 2px 4px;
    border-radius: 3px;
    border: 1px solid #ced4da;
    background-color: white;
    min-width: 80px;
}

.table-monitoring input[type="checkbox"] {
    transform: scale(1.1);
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

<div class="page-header">
    <h4><i class="fas fa-rocket mr-2"></i> Data Submit Fasttrack</h4>
</div>

<div class="navigation-buttons">
    <a href="{{ route('pic.submissions.monitoring') }}" class="btn btn-outline-primary">
        <i class="fas fa-list mr-2"></i>
        Monitoring Submit Normal
    </a>
    <span class="text-muted">|</span>
    <span class="btn btn-primary">
        <i class="fas fa-rocket mr-2"></i>
        Monitoring Submit Fasttrack
    </span>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-tachometer-alt mr-2"></i>
            Monitoring Submit Fasttrack
        </h5>
    </div>
    <div class="card-body">
        <!-- Stats Summary -->
        <div class="row mb-4">
            <div class="col-lg-2 col-md-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h4>{{ $submissions->count() }}</h4>
                        <p>Total Submit</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h4>{{ $submissions->where('is_validated', false)->count() }}</h4>
                        <p>Belum Validasi</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h4>{{ $submissions->where('is_validated', true)->count() }}</h4>
                        <p>Sudah Validasi</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h4>{{ $submissions->whereNotNull('pic_editor1')->count() }}</h4>
                        <p>Ada Editor</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-edit"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h4>{{ $submissions->whereNotNull('pic_reviewer1')->count() }}</h4>
                        <p>Ada Reviewer</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="small-box bg-dark">
                    <div class="inner">
                        <h4>{{ $submissions->whereNotNull('pic_production')->count() }}</h4>
                        <p>Ada Production</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-cogs"></i>
                    </div>
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
                            <strong class="text-primary">{{ $submission->submission_code }}</strong>
                        </td>
                        <td class="sticky-col">
                            <div class="no-wrap" title="{{ $submission->journal_name }}">
                                {{ Str::limit($submission->journal_name, 20) }}
                            </div>
                        </td>
                        <td>
                            <div class="no-wrap" title="{{ $submission->title }}">
                                {{ Str::limit($submission->title, 30) }}
                            </div>
                        </td>
                        <td>
                            <div class="no-wrap" title="{{ $submission->authors }}">
                                {{ Str::limit($submission->authors, 25) }}
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
                        <td>{{ $submission->created_at->format('d/m/Y') }}</td>
                        
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
                                    onchange="updatePicAssignment({{ $submission->id }}, 'pic_editor1', this.value)">
                                <option value="">Pilih Editor 1</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->pic_editor1 == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Editor 2 -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'pic_editor2', this.value)">
                                <option value="">Pilih Editor 2</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->pic_editor2 == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Editor 3 -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'pic_editor3', this.value)">
                                <option value="">Pilih Editor 3</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->pic_editor3 == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Author 1 -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'pic_author1', this.value)">
                                <option value="">Pilih Author 1</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->pic_author1 == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Author 2 -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'pic_author2', this.value)">
                                <option value="">Pilih Author 2</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->pic_author2 == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Reviewer 1 -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'pic_reviewer1', this.value)">
                                <option value="">Pilih Reviewer 1</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->pic_reviewer1 == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Reviewer 2 -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'pic_reviewer2', this.value)">
                                <option value="">Pilih Reviewer 2</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->pic_reviewer2 == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Production -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'pic_production', this.value)">
                                <option value="">Pilih Production</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->pic_production == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Upload Proof -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'pic_upload_proof', this.value)">
                                <option value="">Pilih Upload</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->pic_upload_proof == $pic->id ? 'selected' : '' }}>
                                        {{ $pic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Finalisasi -->
                        <td class="assignment-column">
                            <select class="form-control form-control-sm pic-assignment-select" 
                                    onchange="updatePicAssignment({{ $submission->id }}, 'pic_finalisasi', this.value)">
                                <option value="">Pilih Finalisasi</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->id }}" 
                                            {{ $submission->pic_finalisasi == $pic->id ? 'selected' : '' }}>
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

<script>
// Toggle validation status
function toggleValidation(submissionId, isValidated) {
    fetch(`{{ route('pic.fasttrack.toggle-validation') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            submission_id: submissionId,
            is_validated: isValidated
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update label
            const label = document.querySelector(`label[for="validation_${submissionId}"]`);
            label.textContent = isValidated ? 'Valid' : 'Pending';
            
            // Show success message
            showNotification('Status validasi berhasil diupdate', 'success');
        } else {
            // Revert checkbox
            const checkbox = document.getElementById(`validation_${submissionId}`);
            checkbox.checked = !isValidated;
            showNotification('Gagal update status validasi', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert checkbox
        const checkbox = document.getElementById(`validation_${submissionId}`);
        checkbox.checked = !isValidated;
        showNotification('Terjadi kesalahan', 'error');
    });
}

// Update PIC assignment
function updatePicAssignment(submissionId, field, picId) {
    // Show loading state
    const select = event.target;
    select.classList.add('loading-assignments');
    
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
        select.classList.remove('loading-assignments');
        if (data.success) {
            showNotification(`Berhasil update ${field.replace('pic_', '').replace('_', ' ')}`, 'success');
        } else {
            showNotification('Gagal update assignment', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        select.classList.remove('loading-assignments');
        showNotification('Terjadi kesalahan', 'error');
    });
}

// Show notification
function showNotification(message, type = 'info') {
    // You can implement your preferred notification system here
    // For now, we'll use a simple alert (you might want to use toast notifications)
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    const notification = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" 
             style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', notification);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        if (alerts.length > 0) {
            alerts[alerts.length - 1].remove();
        }
    }, 3000);
}

// Initialize tooltips if using Bootstrap
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endsection