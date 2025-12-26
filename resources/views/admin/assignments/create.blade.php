@extends('layouts.app')

@section('title', 'Assign Reviewer - REVANA')
@section('page-title', 'Tugaskan Reviewer')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.journals.index') }}" class="nav-link">
        <i class="bi bi-journal-text"></i> Jurnal
    </a>
    <a href="{{ route('admin.assignments.index') }}" class="nav-link active">
        <i class="bi bi-clipboard-check"></i> Review Assignments
    </a>
    <a href="{{ route('admin.reviewers.index') }}" class="nav-link">
        <i class="bi bi-people"></i> Reviewers
    </a>
    <a href="{{ route('admin.redemptions.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-plus"></i> Form Assign Reviewer
            </div>
            <div class="card-body">
                <form action="{{ route('admin.assignments.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Pilih Jurnal <span class="text-danger">*</span></label>
                        <select class="form-select @error('journal_id') is-invalid @enderror" 
                                name="journal_id" id="journalSelect" required>
                            <option value="">-- Pilih Jurnal --</option>
                            @foreach($journals as $journal)
                            <option value="{{ $journal->id }}" 
                                    data-title="{{ $journal->title }}"
                                    data-accreditation="{{ $journal->accreditation }}"
                                    data-points="{{ $journal->points }}"
                                    {{ old('journal_id') == $journal->id ? 'selected' : '' }}>
                                {{ Str::limit($journal->title, 80) }} - {{ $journal->accreditation }}
                            </option>
                            @endforeach
                        </select>
                        @error('journal_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="journalInfo" class="alert alert-info" style="display: none;">
                        <strong>Jurnal:</strong> <span id="infoTitle"></span><br>
                        <strong>Akreditasi:</strong> <span id="infoAccreditation"></span><br>
                        <strong>Points Reward:</strong> <span id="infoPoints"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih Reviewer <span class="text-danger">*</span></label>
                        <select class="form-select @error('reviewer_id') is-invalid @enderror" 
                                name="reviewer_id" required>
                            <option value="">-- Pilih Reviewer --</option>
                            @foreach($reviewers as $reviewer)
                            <option value="{{ $reviewer->id }}" {{ old('reviewer_id') == $reviewer->id ? 'selected' : '' }}>
                                {{ $reviewer->name }} - {{ $reviewer->email }}
                                ({{ $reviewer->completed_reviews }} reviews, {{ $reviewer->total_points }} pts)
                            </option>
                            @endforeach
                        </select>
                        @error('reviewer_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Assign Reviewer
                        </button>
                        <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">
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
                <h6>Tips Assign Reviewer:</h6>
                <ul class="small">
                    <li>Pilih reviewer yang sesuai dengan bidang jurnal</li>
                    <li>Perhatikan beban kerja reviewer saat ini</li>
                    <li>Reviewer akan menerima notifikasi tugas baru</li>
                    <li>Reviewer bisa menerima atau menolak tugas</li>
                </ul>
                <hr>
                <p class="mb-0 small text-muted">
                    Setelah reviewer menyelesaikan dan hasil review disetujui, 
                    reviewer akan mendapatkan point sesuai akreditasi jurnal.
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-people"></i> Statistik Reviewer
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Total Reviewer:</strong> {{ $reviewers->count() }}</p>
                <p class="mb-0"><strong>Jurnal Tersedia:</strong> {{ $journals->count() }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('journalSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const infoDiv = document.getElementById('journalInfo');
    
    if (this.value) {
        document.getElementById('infoTitle').textContent = selectedOption.dataset.title;
        document.getElementById('infoAccreditation').textContent = selectedOption.dataset.accreditation;
        document.getElementById('infoPoints').textContent = selectedOption.dataset.points + ' Points';
        infoDiv.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
});
</script>
@endpush
@endsection
