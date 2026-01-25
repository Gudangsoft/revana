@extends('layouts.app')

@section('title', 'Data Jurnal - ' . $appSettings['app_name'])
@section('page-title', 'Data Jurnal')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-bookmark"></i> Data Jurnal</span>
                <div class="btn-group">
                    <button type="button" class="btn btn-danger" id="bulkDeleteBtn" style="display: none;" onclick="confirmBulkDelete()">
                        <i class="bi bi-trash"></i> Hapus <span id="selectedCount">0</span> Terpilih
                    </button>
                    <a href="{{ route('admin.journal-masters.template') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-file-earmark-arrow-down"></i> Template
                    </a>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-upload"></i> Import
                    </button>
                    <a href="{{ route('admin.journal-masters.export', request()->query()) }}" class="btn btn-info">
                        <i class="bi bi-download"></i> Export
                    </a>
                    <a href="{{ route('admin.journal-masters.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Search & Filter Form -->
                <form action="{{ route('admin.journal-masters.index') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" placeholder="🔍 Cari nama jurnal..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="publisher" placeholder="📚 Publisher..." value="{{ request('publisher') }}" list="publisher-list">
                            <datalist id="publisher-list">
                                @php
                                    $publishers = \App\Models\JournalMaster::select('publisher')->distinct()->whereNotNull('publisher')->orderBy('publisher')->get();
                                @endphp
                                @foreach($publishers as $pub)
                                    <option value="{{ $pub->publisher }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="accreditation" placeholder="🏆 Akreditasi..." value="{{ request('accreditation') }}" list="akreditasi-list">
                            <datalist id="akreditasi-list">
                                @foreach($accreditations as $acc)
                                    <option value="{{ $acc->name }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="kategori" placeholder="📂 Kategori..." value="{{ request('kategori') }}" list="kategori-list">
                            <datalist id="kategori-list">
                                <option value="Penelitian">
                                <option value="PKM">
                            </datalist>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="jenis_jurnal" placeholder="🌐 Jenis..." value="{{ request('jenis_jurnal') }}" list="jenis-list">
                            <datalist id="jenis-list">
                                <option value="Jurnal Nasional">
                                <option value="Jurnal Internasional">
                            </datalist>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-primary w-100" type="submit" title="Filter">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        @if(request()->hasAny(['search', 'publisher', 'accreditation', 'status', 'kategori', 'jenis_jurnal']))
                        <div class="col-md-12">
                            <a href="{{ route('admin.journal-masters.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle"></i> Reset Filter
                            </a>
                        </div>
                        @endif
                    </div>
                </form>

                <div class="table-responsive">
                    <form id="bulkDeleteForm" action="{{ route('admin.journal-masters.bulk-delete') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" class="form-check-input" id="selectAll" title="Pilih Semua">
                                    </th>
                                    <th>#</th>
                                    <th>Kode Jurnal</th>
                                    <th>Nama Jurnal</th>
                                    <th>Publisher</th>
                                    <th>Kategori</th>
                                    <th>Jenis</th>
                                    <th>Akreditasi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        <tbody>
                            @forelse($journals as $journal)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input journal-checkbox" name="journal_ids[]" value="{{ $journal->id }}">
                                </td>
                                <td>{{ $loop->iteration + ($journals->currentPage() - 1) * $journals->perPage() }}</td>
                                <td><code>{{ $journal->kode_jurnal }}</code></td>
                                <td>
                                    <strong>{{ Str::limit($journal->nama_jurnal, 40) }}</strong>
                                    @if($journal->link_jurnal)
                                        <a href="{{ $journal->link_jurnal }}" target="_blank" class="ms-1" title="Buka Link">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @endif
                                </td>
                                <td>{{ Str::limit($journal->publisher, 25) }}</td>
                                <td>
                                    @if($journal->kategori)
                                        <span class="badge bg-{{ $journal->kategori == 'Penelitian' ? 'primary' : 'success' }}">{{ $journal->kategori }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($journal->jenis_jurnal)
                                        <span class="badge bg-{{ $journal->jenis_jurnal == 'Jurnal Internasional' ? 'warning' : 'secondary' }}">
                                            {{ $journal->jenis_jurnal == 'Jurnal Internasional' ? 'Internasional' : 'Nasional' }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($journal->accreditation)
                                        <span class="badge bg-info">{{ $journal->accreditation }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($journal->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.journal-masters.show', $journal) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.journal-masters.edit', $journal) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.journal-masters.toggle-active', $journal) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $journal->is_active ? 'btn-secondary' : 'btn-success' }}" title="{{ $journal->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="bi {{ $journal->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.journal-masters.destroy', $journal) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jurnal ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada data jurnal
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </form>
                </div>

                <div class="mt-3">
                    @include('components.simple-pagination', ['paginator' => $journals->withQueryString()])
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel"><i class="bi bi-upload"></i> Import Data Jurnal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.journal-masters.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">File Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">Format: xlsx, xls, csv. Maksimal 5MB</small>
                    </div>
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Petunjuk Import:</h6>
                        <ul class="mb-0 small">
                            <li>Download template terlebih dahulu untuk format yang benar</li>
                            <li>Kolom wajib: <strong>nama_jurnal</strong>, <strong>publisher</strong>, <strong>link_jurnal</strong></li>
                            <li>Kolom opsional: kode_jurnal, akreditasi, status</li>
                            <li>Jika kode_jurnal kosong, akan digenerate otomatis</li>
                            <li>Jika nama jurnal sudah ada, data akan diperbarui</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const journalCheckboxes = document.querySelectorAll('.journal-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    // Select/Deselect all
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            journalCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });
    }
    
    // Individual checkbox change
    journalCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
            updateBulkDeleteButton();
        });
    });
    
    // Update select all checkbox state
    function updateSelectAllState() {
        const checkedCount = document.querySelectorAll('.journal-checkbox:checked').length;
        const totalCount = journalCheckboxes.length;
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = checkedCount === totalCount && totalCount > 0;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
        }
    }
    
    // Show/hide bulk delete button based on selection
    function updateBulkDeleteButton() {
        const checkedCount = document.querySelectorAll('.journal-checkbox:checked').length;
        
        if (checkedCount > 0) {
            bulkDeleteBtn.style.display = 'inline-block';
            selectedCountSpan.textContent = checkedCount;
        } else {
            bulkDeleteBtn.style.display = 'none';
        }
    }
});

function confirmBulkDelete() {
    const checkedCount = document.querySelectorAll('.journal-checkbox:checked').length;
    
    if (checkedCount === 0) {
        alert('Pilih minimal 1 jurnal untuk dihapus');
        return;
    }
    
    const confirmMsg = `⚠️ PERINGATAN!\\n\\nAnda akan menghapus PERMANEN ${checkedCount} jurnal.\\n\\nData yang dihapus TIDAK BISA dikembalikan!\\n\\nKetik \"HAPUS\" untuk konfirmasi:`;
    
    const userInput = prompt(confirmMsg);
    
    if (userInput === 'HAPUS') {
        document.getElementById('bulkDeleteForm').submit();
    } else if (userInput !== null) {
        alert('Konfirmasi gagal. Penghapusan dibatalkan.');
    }
}
</script>
@endsection
