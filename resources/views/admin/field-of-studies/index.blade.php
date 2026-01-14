@extends('layouts.app')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Manajemen Bidang Ilmu</h1>
        <div class="btn-group">
            <button type="button" class="btn btn-danger" id="bulkDeleteBtn" style="display: none;" data-bs-toggle="modal" data-bs-target="#bulkDeleteModal">
                <i class="fas fa-trash"></i> Hapus Massal (<span id="selectedCount">0</span>)
            </button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-excel"></i> Import Excel
            </button>
            <a href="{{ route('admin.field-of-studies.template') }}" class="btn btn-info">
                <i class="fas fa-download"></i> Template
            </a>
            <a href="{{ route('admin.field-of-studies.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th style="width: 50px;">#</th>
                            <th>Nama Bidang Ilmu</th>
                            <th style="width: 120px;">Urutan</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 120px;">Reviewer</th>
                            <th style="width: 120px;">Pendaftar</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fields as $field)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input field-checkbox" value="{{ $field->id }}" data-name="{{ $field->name }}">
                                </td>
                                <td>{{ ($fields->currentPage() - 1) * $fields->perPage() + $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $field->name }}</strong>
                                    @if($field->description)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($field->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $field->order }}</span>
                                </td>
                                <td>
                                    @if($field->is_active)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times"></i> Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $field->users_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $field->reviewer_registrations_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-cog"></i> Aksi
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.field-of-studies.edit', $field) }}">
                                                    <i class="fas fa-edit text-warning"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.field-of-studies.toggle', $field) }}" method="POST">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-toggle-{{ $field->is_active ? 'on' : 'off' }} text-{{ $field->is_active ? 'secondary' : 'success' }}"></i>
                                                        {{ $field->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $field->id }}">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal{{ $field->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Apakah Anda yakin ingin menghapus bidang ilmu <strong>{{ $field->name }}</strong>?</p>
                                                    @if($field->users_count > 0 || $field->reviewer_registrations_count > 0)
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            Bidang ilmu ini sedang digunakan oleh:
                                                            <ul class="mb-0 mt-2">
                                                                @if($field->users_count > 0)
                                                                    <li>{{ $field->users_count }} reviewer</li>
                                                                @endif
                                                                @if($field->reviewer_registrations_count > 0)
                                                                    <li>{{ $field->reviewer_registrations_count }} pendaftar</li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <form action="{{ route('admin.field-of-studies.destroy', $field) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Hapus</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data bidang ilmu.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($fields->hasPages())
                <div class="mt-3">
                    {{ $fields->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="fas fa-file-excel"></i> Import Bidang Ilmu dari Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.field-of-studies.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Format File Excel:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Kolom: <code>name</code>, <code>description</code>, <code>order</code>, <code>is_active</code></li>
                                <li>Atau: <code>nama</code>, <code>deskripsi</code>, <code>urutan</code>, <code>status</code></li>
                                <li>Baris pertama adalah header</li>
                                <li>Format: .xlsx, .xls, atau .csv</li>
                                <li>Maksimal 2MB</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">
                                Pilih File Excel <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control @error('file') is-invalid @enderror" 
                                   id="file" 
                                   name="file" 
                                   accept=".xlsx,.xls,.csv"
                                   required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Catatan:</strong> Jika bidang ilmu dengan nama yang sama sudah ada, data akan diupdate.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload"></i> Upload & Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Modal -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus Massal Permanen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="bulkDeleteForm" action="{{ route('admin.field-of-studies.bulk-delete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>PERINGATAN:</strong> Tindakan ini tidak dapat dibatalkan!
                        </div>
                        <p>Anda akan menghapus <strong><span id="deleteCount">0</span></strong> bidang ilmu secara permanen:</p>
                        <div id="selectedList" class="alert alert-warning" style="max-height: 200px; overflow-y: auto;">
                            <!-- List will be populated by JavaScript -->
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmDelete" required>
                            <label class="form-check-label text-danger" for="confirmDelete">
                                <strong>Saya yakin ingin menghapus data ini secara permanen</strong>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                            <i class="fas fa-trash"></i> Hapus Permanen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.field-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCount = document.getElementById('selectedCount');
    const deleteCount = document.getElementById('deleteCount');
    const selectedList = document.getElementById('selectedList');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const confirmDelete = document.getElementById('confirmDelete');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    // Select All functionality
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkDeleteButton();
    });

    // Individual checkbox change
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkDeleteButton();
            updateSelectAllCheckbox();
        });
    });

    // Update Select All checkbox state
    function updateSelectAllCheckbox() {
        const totalCheckboxes = checkboxes.length;
        const checkedCheckboxes = document.querySelectorAll('.field-checkbox:checked').length;
        selectAll.checked = totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0;
        selectAll.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
    }

    // Update bulk delete button visibility and count
    function updateBulkDeleteButton() {
        const selectedCheckboxes = document.querySelectorAll('.field-checkbox:checked');
        const count = selectedCheckboxes.length;
        
        if (count > 0) {
            bulkDeleteBtn.style.display = 'inline-block';
            selectedCount.textContent = count;
        } else {
            bulkDeleteBtn.style.display = 'none';
        }
    }

    // When modal is shown, populate the list
    document.getElementById('bulkDeleteModal').addEventListener('show.bs.modal', function() {
        const selectedCheckboxes = document.querySelectorAll('.field-checkbox:checked');
        
        // Prevent modal from showing if no checkbox is selected
        if (selectedCheckboxes.length === 0) {
            alert('Silakan pilih minimal 1 bidang ilmu untuk dihapus');
            return false;
        }
        
        const ids = [];
        let listHtml = '<ul class="mb-0">';
        
        selectedCheckboxes.forEach(checkbox => {
            ids.push(checkbox.value);
            listHtml += '<li>' + checkbox.dataset.name + '</li>';
        });
        
        listHtml += '</ul>';
        
        deleteCount.textContent = ids.length;
        selectedList.innerHTML = listHtml;
        
        // Clear existing hidden inputs
        bulkDeleteForm.querySelectorAll('input[name="ids[]"]').forEach(input => input.remove());
        
        // Add hidden inputs for selected IDs
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            bulkDeleteForm.appendChild(input);
        });
        
        // Reset confirmation checkbox
        confirmDelete.checked = false;
        confirmDeleteBtn.disabled = true;
    });

    // Form submit validation
    bulkDeleteForm.addEventListener('submit', function(e) {
        const selectedIds = bulkDeleteForm.querySelectorAll('input[name="ids[]"]');
        
        if (selectedIds.length === 0) {
            e.preventDefault();
            alert('Tidak ada bidang ilmu yang dipilih');
            return false;
        }
        
        if (!confirmDelete.checked) {
            e.preventDefault();
            alert('Silakan centang konfirmasi untuk melanjutkan');
            return false;
        }
        
        return true;
    });

    // Enable/disable delete button based on confirmation
    confirmDelete.addEventListener('change', function() {
        confirmDeleteBtn.disabled = !this.checked;
    });
});
</script>
@endsection
