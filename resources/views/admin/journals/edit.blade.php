@extends('layouts.app')

@section('title', 'Edit Jurnal - REVANA')
@section('page-title', 'Edit Jurnal')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.journals.index') }}" class="nav-link active">
        <i class="bi bi-journal-text"></i> Jurnal
    </a>
    <a href="{{ route('admin.assignments.index') }}" class="nav-link">
        <i class="bi bi-clipboard-check"></i> Review Assignments
    </a>
    <a href="{{ route('admin.reviewers.index') }}" class="nav-link">
        <i class="bi bi-people"></i> Reviewers
    </a>
    <a href="{{ route('admin.leaderboard.index') }}" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="{{ route('admin.redemptions.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
    <a href="{{ route('admin.points.index') }}" class="nav-link">
        <i class="bi bi-coin"></i> Point Management
    </a>
    <a href="{{ route('admin.rewards.index') }}" class="nav-link">
        <i class="bi bi-trophy"></i> Reward Management
    </a>
    <a href="{{ route('admin.marketings.index') }}" class="nav-link">
        <i class="bi bi-megaphone"></i> Marketing
    </a>
    <a href="{{ route('admin.pics.index') }}" class="nav-link">
        <i class="bi bi-person-badge"></i> PIC
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil-square"></i> Form Edit Jurnal
            </div>
            <div class="card-body">
                <form action="{{ route('admin.journals.update', $journal) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Judul Jurnal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               name="title" value="{{ old('title', $journal->title) }}" required>
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link Jurnal <span class="text-danger">*</span></label>
                        <input type="url" class="form-control @error('link') is-invalid @enderror" 
                               name="link" value="{{ old('link', $journal->link) }}" 
                               placeholder="https://..." required>
                        @error('link')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Akreditasi <span class="text-danger">*</span></label>
                        <select class="form-select @error('accreditation') is-invalid @enderror" 
                                name="accreditation" required>
                            <option value="">Pilih Akreditasi</option>
                            <option value="SINTA 1" {{ old('accreditation', $journal->accreditation) == 'SINTA 1' ? 'selected' : '' }}>SINTA 1 (100 points)</option>
                            <option value="SINTA 2" {{ old('accreditation', $journal->accreditation) == 'SINTA 2' ? 'selected' : '' }}>SINTA 2 (80 points)</option>
                            <option value="SINTA 3" {{ old('accreditation', $journal->accreditation) == 'SINTA 3' ? 'selected' : '' }}>SINTA 3 (60 points)</option>
                            <option value="SINTA 4" {{ old('accreditation', $journal->accreditation) == 'SINTA 4' ? 'selected' : '' }}>SINTA 4 (40 points)</option>
                            <option value="SINTA 5" {{ old('accreditation', $journal->accreditation) == 'SINTA 5' ? 'selected' : '' }}>SINTA 5 (20 points)</option>
                            <option value="SINTA 6" {{ old('accreditation', $journal->accreditation) == 'SINTA 6' ? 'selected' : '' }}>SINTA 6 (10 points)</option>
                        </select>
                        @error('accreditation')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Points akan diberikan otomatis berdasarkan akreditasi</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Terbitan</label>
                        <input type="text" class="form-control @error('publisher') is-invalid @enderror" 
                               name="publisher" value="{{ old('publisher', $journal->publisher) }}" 
                               placeholder="Nama penerbit jurnal">
                        @error('publisher')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Marketing 
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMarketingModal">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </label>
                        <select class="form-select @error('marketing') is-invalid @enderror" 
                                name="marketing" id="marketing_select">
                            <option value="">Pilih Marketing</option>
                            @foreach($marketings as $marketing)
                                <option value="{{ $marketing->name }}" {{ old('marketing', $journal->marketing) == $marketing->name ? 'selected' : '' }}>
                                    {{ $marketing->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('marketing')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">PIC (Person In Charge) 
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPicModal">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </label>
                        <select class="form-select @error('pic') is-invalid @enderror" 
                                name="pic" id="pic_select">
                            <option value="">Pilih PIC</option>
                            @foreach($pics as $pic)
                                <option value="{{ $pic->name }}" {{ old('pic', $journal->pic) == $pic->name ? 'selected' : '' }}>
                                    {{ $pic->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('pic')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">User Author</label>
                        <input type="text" class="form-control @error('author_name') is-invalid @enderror" 
                               name="author_name" value="{{ old('author_name', $journal->author_name) }}" 
                               placeholder="User author jurnal">
                        @error('author_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link Turnitin</label>
                        <input type="url" class="form-control @error('turnitin_link') is-invalid @enderror" 
                               name="turnitin_link" value="{{ old('turnitin_link', $journal->turnitin_link) }}" 
                               placeholder="https://...">
                        @error('turnitin_link')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                        <a href="{{ route('admin.journals.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Informasi
            </div>
            <div class="card-body">
                <h6>Sistem Point:</h6>
                <ul class="list-unstyled">
                    <li>🥇 SINTA 1 = 100 points</li>
                    <li>🥈 SINTA 2 = 80 points</li>
                    <li>🥉 SINTA 3 = 60 points</li>
                    <li>📊 SINTA 4 = 40 points</li>
                    <li>📈 SINTA 5 = 20 points</li>
                    <li>📉 SINTA 6 = 10 points</li>
                </ul>
                <hr>
                <small class="text-muted">
                    Point akan otomatis diberikan ke reviewer setelah hasil review disetujui.
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Marketing -->
<div class="modal fade" id="addMarketingModal" tabindex="-1" aria-labelledby="addMarketingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMarketingModalLabel">
                    <i class="bi bi-megaphone"></i> Tambah Marketing Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addMarketingForm" action="{{ route('admin.marketings.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Marketing <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="marketing_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="marketing_email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" class="form-control" name="phone" id="marketing_phone">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="marketing_is_active" value="1" checked>
                        <label class="form-check-label" for="marketing_is_active">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add PIC -->
<div class="modal fade" id="addPicModal" tabindex="-1" aria-labelledby="addPicModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPicModalLabel">
                    <i class="bi bi-person-badge"></i> Tambah PIC Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPicForm" action="{{ route('admin.pics.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama PIC <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="pic_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="pic_email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" class="form-control" name="phone" id="pic_phone">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="pic_is_active" value="1" checked>
                        <label class="form-check-label" for="pic_is_active">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-fill marketing name after adding via modal
document.getElementById('addMarketingForm').addEventListener('submit', function(e) {
    const marketingName = document.getElementById('marketing_name').value;
    if (marketingName) {
        const selectElement = document.getElementById('marketing_select');
        // Add new option
        const newOption = new Option(marketingName, marketingName, true, true);
        selectElement.add(newOption);
    }
});

// Auto-fill PIC name after adding via modal
document.getElementById('addPicForm').addEventListener('submit', function(e) {
    const picName = document.getElementById('pic_name').value;
    if (picName) {
        const selectElement = document.getElementById('pic_select');
        // Add new option
        const newOption = new Option(picName, picName, true, true);
        selectElement.add(newOption);
    }
});
</script>
@endsection

