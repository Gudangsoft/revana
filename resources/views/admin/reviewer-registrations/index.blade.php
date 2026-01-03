@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Pendaftaran Reviewer')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-plus"></i> Daftar Pendaftaran Reviewer</span>
                <div>
                    <span class="badge bg-warning text-dark">Pending: {{ $registrations->where('status', 'pending')->count() }}</span>
                    <span class="badge bg-success">Approved: {{ $registrations->where('status', 'approved')->count() }}</span>
                    <span class="badge bg-danger">Rejected: {{ $registrations->where('status', 'rejected')->count() }}</span>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.reviewer-registrations.bulk-approve') }}" method="POST" id="bulkApproveForm">
                    @csrf
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div>
                            <button type="submit" class="btn btn-success" id="bulkApproveBtn" disabled>
                                <i class="bi bi-check-circle"></i> Approve Terpilih (<span id="selectedCount">0</span>)
                            </button>
                        </div>
                        <div>
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" id="selectAll"> Pilih Semua Pending
                            </label>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" class="form-check-input" id="selectAllHeader">
                                    </th>
                                    <th>Tanggal</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Afiliasi</th>
                                <th>Bidang Ilmu</th>
                                <th>Bahasa</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $registration)
                            <tr>
                                <td>
                                    @if($registration->status === 'pending')
                                        <input type="checkbox" name="registration_ids[]" value="{{ $registration->id }}" class="form-check-input registration-checkbox">
                                    @endif
                                </td>
                                <td>{{ $registration->created_at->format('d M Y') }}</td>
                                <td><strong>{{ $registration->full_name }}</strong></td>
                                <td>{{ $registration->email }}</td>
                                <td>{{ $registration->affiliation }}</td>
                                <td>{{ $registration->field_of_study }}</td>
                                <td>
                                    @if($registration->article_languages && is_array($registration->article_languages))
                                        @foreach($registration->article_languages as $lang)
                                            <span class="badge bg-secondary">{{ $lang }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($registration->status === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock"></i> Pending
                                        </span>
                                    @elseif($registration->status === 'approved')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Approved
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> Rejected
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.reviewer-registrations.show', $registration) }}" 
                                       class="btn btn-sm btn-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($registration->status === 'pending')
                                        <form action="{{ route('admin.reviewer-registrations.approve', $registration) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" 
                                                    onclick="return confirm('Setujui pendaftaran ini dan buat akun reviewer?')"
                                                    title="Approve">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#rejectModal{{ $registration->id }}"
                                                title="Reject">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    @endif
                                    <form action="{{ route('admin.reviewer-registrations.destroy', $registration) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('Hapus data pendaftaran ini?')"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $registration->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.reviewer-registrations.reject', $registration) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Tolak Pendaftaran</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Tolak pendaftaran dari <strong>{{ $registration->full_name }}</strong>?</p>
                                                <div class="mb-3">
                                                    <label for="notes" class="form-label">Alasan Penolakan</label>
                                                    <textarea class="form-control" name="notes" rows="3" required 
                                                              placeholder="Masukkan alasan penolakan..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">Tolak</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="text-muted mt-2">Belum ada pendaftaran reviewer</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>
                </div>

                <div class="mt-3">
                    {{ $registrations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllTop = document.getElementById('selectAll');
    const selectAllHeader = document.getElementById('selectAllHeader');
    const checkboxes = document.querySelectorAll('.registration-checkbox');
    const bulkApproveBtn = document.getElementById('bulkApproveBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    const bulkApproveForm = document.getElementById('bulkApproveForm');

    function updateSelectedCount() {
        const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
        const count = checkedBoxes.length;
        selectedCountSpan.textContent = count;
        bulkApproveBtn.disabled = count === 0;
    }

    // Sync both select all checkboxes
    function syncSelectAll() {
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        selectAllTop.checked = allChecked;
        selectAllHeader.checked = allChecked;
    }

    // Select all functionality
    [selectAllTop, selectAllHeader].forEach(selectAll => {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            syncSelectAll();
            updateSelectedCount();
        });
    });

    // Individual checkbox change
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            syncSelectAll();
            updateSelectedCount();
        });
    });

    // Form submission confirmation
    bulkApproveForm.addEventListener('submit', function(e) {
        const checkedCount = document.querySelectorAll('.registration-checkbox:checked').length;
        if (checkedCount === 0) {
            e.preventDefault();
            alert('Pilih minimal 1 pendaftaran untuk di-approve');
            return false;
        }
        
        if (!confirm(`Approve ${checkedCount} pendaftaran reviewer dan buat akun mereka?`)) {
            e.preventDefault();
            return false;
        }
    });

    updateSelectedCount();
});
</script>
@endpush
@endsection
