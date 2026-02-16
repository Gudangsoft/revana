@extends('marketing.layouts.app')

@section('title', 'Detail Artikel')

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('marketing.submissions') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Artikel
            </a>
        </div>

        <!-- Submission Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> {{ $submission->kode_submit }}
                    </h5>
                    <span class="badge bg-light text-dark">
                        {{ $submission->status }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Kode Submit:</th>
                                <td><code>{{ $submission->kode_submit }}</code></td>
                            </tr>
                            <tr>
                                <th>ID Artikel:</th>
                                <td>{{ $submission->id_artikel ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jurnal:</th>
                                <td>{{ $submission->journalSlot->journalMaster->nama_jurnal ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Volume/Nomor:</th>
                                <td>Vol. {{ $submission->journalSlot->volume }}, No. {{ $submission->journalSlot->nomor }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Submit:</th>
                                <td>{{ $submission->tanggal_submit ? \Carbon\Carbon::parse($submission->tanggal_submit)->format('d M Y H:i') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Penulis:</th>
                                <td>{{ $submission->nama_penulis }}</td>
                            </tr>
                            <tr>
                                <th>No HP:</th>
                                <td>{{ $submission->no_hp_penulis ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Link Submit:</th>
                                <td>
                                    @if($submission->link_artikel)
                                        <a href="{{ $submission->link_artikel }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-link-45deg"></i> Buka Link
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Username Author:</th>
                                <td><code>{{ $submission->username_author ?? '-' }}</code></td>
                            </tr>
                            <tr>
                                <th>Password Author:</th>
                                <td><code>{{ $submission->password_author ?? '-' }}</code></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6 class="text-muted">Judul Artikel:</h6>
                    <p class="lead">{{ $submission->judul_artikel }}</p>
                </div>
            </div>
        </div>

        <!-- Progress Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-diagram-3"></i> Tracking Proses Review</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Tahap</th>
                                <th>Petugas</th>
                                <th>Credential</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Submit -->
                            <tr>
                                <td><strong>Submit</strong></td>
                                <td>{{ $submission->petugasSubmit->name ?? '-' }}</td>
                                <td>-</td>
                                <td>
                                    @if($submission->petugasSubmit)
                                        <span class="badge bg-success">Assigned</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Editor 1 -->
                            <tr class="table-info">
                                <td><strong>Editor 1 (E1)</strong></td>
                                <td>{{ $submission->petugasEditor1->name ?? '-' }}</td>
                                <td>
                                    @if($submission->username_editor && $submission->password_editor)
                                        <code>{{ $submission->username_editor }} / {{ $submission->password_editor }}</code>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($submission->editor1_valid)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                                    @elseif($submission->petugasEditor1)
                                        <span class="badge bg-warning">In Progress</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Author 1 -->
                            <tr class="table-warning">
                                <td><strong>Author 1 (A1)</strong></td>
                                <td>{{ $submission->petugasAuthor1->name ?? '-' }}</td>
                                <td>-</td>
                                <td>
                                    @if($submission->author1_valid)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                                    @elseif($submission->petugasAuthor1)
                                        <span class="badge bg-warning">In Progress</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Editor 2 -->
                            <tr class="table-info">
                                <td><strong>Editor 2 (E2)</strong></td>
                                <td>{{ $submission->petugasEditor2->name ?? '-' }}</td>
                                <td>-</td>
                                <td>
                                    @if($submission->editor2_valid)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                                    @elseif($submission->petugasEditor2)
                                        <span class="badge bg-warning">In Progress</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Reviewer 1 -->
                            <tr class="table-primary">
                                <td><strong>Reviewer 1 (R1)</strong></td>
                                <td>{{ $submission->petugasReviewer1->name ?? '-' }}</td>
                                <td>
                                    @if($submission->username_reviewer1 && $submission->password_reviewer1)
                                        <code>{{ $submission->username_reviewer1 }} / {{ $submission->password_reviewer1 }}</code>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($submission->reviewer1_valid)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                                    @elseif($submission->petugasReviewer1)
                                        <span class="badge bg-warning">In Progress</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Reviewer 2 -->
                            <tr class="table-primary">
                                <td><strong>Reviewer 2 (R2)</strong></td>
                                <td>{{ $submission->petugasReviewer2->name ?? '-' }}</td>
                                <td>
                                    @if($submission->username_reviewer2 && $submission->password_reviewer2)
                                        <code>{{ $submission->username_reviewer2 }} / {{ $submission->password_reviewer2 }}</code>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($submission->reviewer2_valid)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                                    @elseif($submission->petugasReviewer2)
                                        <span class="badge bg-warning">In Progress</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Editor 3 -->
                            <tr class="table-info">
                                <td><strong>Editor 3 (E3)</strong></td>
                                <td>{{ $submission->petugasEditor3->name ?? '-' }}</td>
                                <td>-</td>
                                <td>
                                    @if($submission->editor3_valid)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                                    @elseif($submission->petugasEditor3)
                                        <span class="badge bg-warning">In Progress</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Author 2 -->
                            <tr class="table-warning">
                                <td><strong>Author 2 (A2)</strong></td>
                                <td>{{ $submission->petugasAuthor2->name ?? '-' }}</td>
                                <td>-</td>
                                <td>
                                    @if($submission->author2_valid)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                                    @elseif($submission->petugasAuthor2)
                                        <span class="badge bg-warning">In Progress</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Production -->
                            <tr class="table-success">
                                <td><strong>Production (P)</strong></td>
                                <td>{{ $submission->petugasProduction->name ?? '-' }}</td>
                                <td>
                                    @if($submission->link_publish)
                                        <a href="{{ $submission->link_publish }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-link-45deg"></i> Link Publish
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($submission->production_valid)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Published</span>
                                    @elseif($submission->petugasProduction)
                                        <span class="badge bg-warning">In Progress</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i> <strong>Keterangan:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li><span class="badge bg-secondary">Pending</span> - Belum ada petugas yang ditugaskan</li>
                        <li><span class="badge bg-warning">In Progress</span> - Petugas sedang mengerjakan</li>
                        <li><span class="badge bg-success">Valid/Published</span> - Tahap sudah selesai</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Catatan Marketing -->
        <div class="card mt-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Catatan Marketing</h5>
            </div>
            <div class="card-body">
                @if(session('catatan_success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('catatan_success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('marketing.submissions.update-catatan', $submission) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="catatan_marketing" class="form-label">Catatan dari Marketing <small class="text-muted">(jika ada revisi setelah di submit/proses)</small></label>
                        <textarea name="catatan_marketing" id="catatan_marketing" class="form-control" rows="4"
                                  placeholder="Tulis catatan di sini...">{{ old('catatan_marketing', $submission->catatan_marketing) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Simpan Catatan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
