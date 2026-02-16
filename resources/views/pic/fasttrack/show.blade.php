@extends('pic.layouts.app')

@section('title', 'Detail Fasttrack')
@section('page-title', 'Detail Fasttrack')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <span><i class="bi bi-lightning-charge"></i> Detail Submission Fasttrack</span>
                <div>
                    @php
                        $editCount = $submission->edit_count ?? 0;
                        $maxEditCount = \App\Services\FeatureSettingService::limit('max_fasttrack_edits');
                        $canEdit = $editCount < $maxEditCount;
                    @endphp
                    @if($canEdit)
                        <a href="{{ route('pic.fasttrack.edit', $submission) }}" class="btn btn-primary btn-sm me-2">
                            <i class="bi bi-pencil-square"></i> Edit ({{ $maxEditCount - $editCount }}x tersisa)
                        </a>
                    @else
                        <button class="btn btn-secondary btn-sm me-2" disabled title="Batas edit sudah tercapai">
                            <i class="bi bi-lock"></i> Edit Terkunci
                        </button>
                    @endif
                    <a href="{{ route('pic.fasttrack.index') }}" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Info Badge -->
                <div class="mb-4">
                    <span class="badge bg-warning text-dark fs-6"><i class="bi bi-lightning-charge"></i> Fasttrack</span>
                    <span class="badge bg-success fs-6">Published</span>
                    @php
                        $editCount = $submission->edit_count ?? 0;
                        $maxEditCount = \App\Services\FeatureSettingService::limit('max_fasttrack_edits');
                        $remainingEdits = $maxEditCount - $editCount;
                    @endphp
                    @if($editCount > 0)
                        <span class="badge {{ $remainingEdits == 0 ? 'bg-danger' : ($remainingEdits == 1 ? 'bg-warning text-dark' : 'bg-info') }} fs-6">
                            <i class="bi bi-pencil"></i> Diedit {{ $editCount }}x
                        </span>
                    @endif
                </div>

                <!-- Kode Submit -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Kode Submit</h6>
                                <h4 class="text-warning mb-0"><code>{{ $submission->kode_submit }}</code></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Kode LOA</h6>
                                <h4 class="mb-0"><code>{{ $submission->kode_loa }}</code></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Jurnal -->
                <h6 class="text-muted mb-3 border-bottom pb-2"><i class="bi bi-journal-text"></i> Data Jurnal</h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="40%">Nama Jurnal</td>
                                <td><strong>{{ $submission->journalSlot->journalMaster->nama_jurnal ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Publisher</td>
                                <td>{{ $submission->journalSlot->journalMaster->publisher ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Akreditasi</td>
                                <td>
                                    @if($submission->journalSlot->journalMaster->accreditation ?? null)
                                        <span class="badge bg-info">{{ $submission->journalSlot->journalMaster->accreditation }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="40%">Volume</td>
                                <td>{{ $submission->journalSlot->volume ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Nomor</td>
                                <td>{{ $submission->journalSlot->nomor ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tahun</td>
                                <td>{{ $submission->journalSlot->tahun ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Data Artikel -->
                <h6 class="text-muted mb-3 border-bottom pb-2"><i class="bi bi-file-text"></i> Data Artikel</h6>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="20%">Judul Artikel</td>
                                <td><strong>{{ $submission->judul_artikel }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Link Publish</td>
                                <td>
                                    @if($submission->link_publish)
                                        <a href="{{ $submission->link_publish }}" target="_blank" class="btn btn-success btn-sm">
                                            <i class="bi bi-box-arrow-up-right"></i> Buka Link Publish
                                        </a>
                                        <br><small class="text-muted">{{ $submission->link_publish }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal Submit</td>
                                <td>{{ $submission->tanggal_submit ? $submission->tanggal_submit->format('d F Y') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Data Penulis -->
                <h6 class="text-muted mb-3 border-bottom pb-2"><i class="bi bi-person"></i> Data Penulis</h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="40%">Nama Penulis</td>
                                <td><strong>{{ $submission->nama_penulis }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">No HP</td>
                                <td>{{ $submission->no_hp_penulis ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- PIC & Marketing -->
                <h6 class="text-muted mb-3 border-bottom pb-2"><i class="bi bi-people"></i> PIC & Marketing</h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="40%">Marketing</td>
                                <td>{{ $submission->marketing->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">PIC Submit</td>
                                <td>{{ $submission->petugasSubmit->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Catatan -->
                @if($submission->notes)
                <h6 class="text-muted mb-3 border-bottom pb-2"><i class="bi bi-chat-left-text"></i> Catatan</h6>
                <div class="alert alert-secondary">
                    {{ $submission->notes }}
                </div>
                @endif

                <!-- History -->
                @if($submission->histories && $submission->histories->count() > 0)
                <h6 class="text-muted mb-3 border-bottom pb-2"><i class="bi bi-clock-history"></i> History</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Step</th>
                                <th>Action</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($submission->histories as $history)
                            <tr>
                                <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                <td><span class="badge bg-secondary">{{ $history->step }}</span></td>
                                <td>{{ $history->action }}</td>
                                <td>{{ $history->notes ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
