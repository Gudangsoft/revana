@extends('pic.layouts.app')

@section('title', 'Proses Submission')
@section('page-title', 'Proses Submission')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('styles')
<style>
    .process-card {
        border-left: 4px solid #ffc107;
    }
    .info-label {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }
    .info-value {
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<!-- Current Task Alert -->
<div class="alert alert-warning d-flex align-items-center mb-4">
    <i class="bi bi-person-gear me-3" style="font-size: 2rem;"></i>
    <div>
        <h5 class="mb-1">Anda bertugas sebagai: <strong>{{ $currentRole }}</strong></h5>
        <p class="mb-0">Status saat ini: <span class="badge bg-primary">{{ str_replace('_', ' ', $submission->status) }}</span></p>
    </div>
</div>

<!-- Submission Info -->
<div class="card mb-4 process-card">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-file-earmark-text"></i> Informasi Submission</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <div class="info-label">Kode Submit</div>
                    <div class="info-value"><code>{{ $submission->kode_submit }}</code></div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Judul Artikel</div>
                    <div class="info-value text-primary">{{ $submission->judul_artikel }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Nama Penulis</div>
                    <div class="info-value">{{ $submission->nama_penulis }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <div class="info-label">Jurnal</div>
                    <div class="info-value">
                        @if($submission->journalSlot && $submission->journalSlot->journalMaster)
                            {{ $submission->journalSlot->journalMaster->nama_jurnal }}
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Slot</div>
                    <div class="info-value">
                        @if($submission->journalSlot)
                            Vol. {{ $submission->journalSlot->volume }} No. {{ $submission->journalSlot->nomor }} ({{ $submission->journalSlot->tahun }})
                        @else
                            -
                        @endif
                    </div>
                </div>
                @if($submission->link_artikel)
                <div class="mb-3">
                    <div class="info-label">Link Artikel</div>
                    <div class="info-value">
                        <a href="{{ $submission->link_artikel }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-link-45deg"></i> Buka Artikel
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        @if($submission->username_author || $submission->password_author)
        <hr>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <div class="info-label">Username Author (OJS)</div>
                    <div class="info-value"><code>{{ $submission->username_author ?? '-' }}</code></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <div class="info-label">Password Author (OJS)</div>
                    <div class="info-value"><code>{{ $submission->password_author ?? '-' }}</code></div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Action Cards -->
<div class="row">
    <!-- Validate / Approve -->
    <div class="col-md-6 mb-4">
        <div class="card h-100 border-success">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-check-circle"></i> Validasi & Lanjutkan</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">Klik tombol di bawah jika pekerjaan pada tahap ini sudah selesai dan siap untuk dilanjutkan ke tahap berikutnya.</p>
                
                <form action="{{ route('pic.submissions.validate-step', $submission) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Yakin ingin memvalidasi tahap ini?')">
                        <i class="bi bi-check-lg"></i> Validasi & Lanjutkan ke Tahap Berikutnya
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Request Revision -->
    <div class="col-md-6 mb-4">
        <div class="card h-100 border-warning">
            <div class="card-header bg-warning">
                <h6 class="mb-0"><i class="bi bi-arrow-repeat"></i> Minta Revisi</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">Jika artikel memerlukan perbaikan, kirim permintaan revisi dengan catatan yang jelas.</p>
                
                <form action="{{ route('pic.submissions.request-revision', $submission) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Catatan Revisi <span class="text-danger">*</span></label>
                        <textarea name="revision_notes" class="form-control" rows="3" required placeholder="Jelaskan apa yang perlu direvisi..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Yakin ingin meminta revisi?')">
                        <i class="bi bi-arrow-repeat"></i> Kirim Permintaan Revisi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- History -->
@if($submission->histories && $submission->histories->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Proses</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submission->histories->sortByDesc('created_at') as $history)
                    <tr>
                        <td><small>{{ $history->created_at->format('d/m/Y H:i') }}</small></td>
                        <td><span class="badge bg-secondary">{{ str_replace('_', ' ', $history->status) }}</span></td>
                        <td><small class="text-muted">{{ $history->notes ?? '-' }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="d-flex gap-2">
    <a href="{{ route('pic.submissions.show', $submission) }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke Detail
    </a>
    <a href="{{ route('pic.my-tasks.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-list-check"></i> Kembali ke Tugas Saya
    </a>
</div>
@endsection
