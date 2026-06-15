@extends('layouts.app')

@section('title', 'Detail Fasttrack - ' . $appSettings['app_name'])
@section('page-title', 'Detail Submission Fasttrack')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card mb-4">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <span><i class="bi bi-lightning-charge"></i> Detail Submission Fasttrack</span>
                <span class="badge bg-dark">{{ $submission->kode_submit }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="40%">Kode Submit</td>
                                <td><strong>{{ $submission->kode_submit }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status</td>
                                <td>
                                    <span class="badge bg-success">{{ $submission->status }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tipe Proses</td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-lightning-charge"></i> FASTTRACK
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal Input</td>
                                <td>{{ $submission->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="40%">PIC Marketing</td>
                                <td>{{ $submission->marketing->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">PIC Submit</td>
                                <td>{{ $submission->petugasSubmit->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>
                <h6 class="text-muted mb-3"><i class="bi bi-journal-text"></i> Data Jurnal</h6>

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
                                    @php
                                        $akreditasi = $submission->journalSlot->journalMaster->accreditation ?? null;
                                    @endphp
                                    @if($akreditasi)
                                        @php
                                            $badgeClass = match(strtoupper($akreditasi)) {
                                                'SINTA 1', 'S1' => 'bg-success',
                                                'SINTA 2', 'S2' => 'bg-primary',
                                                'SINTA 3', 'S3' => 'bg-info',
                                                'SINTA 4', 'S4' => 'bg-warning text-dark',
                                                'SINTA 5', 'S5' => 'bg-secondary',
                                                'SINTA 6', 'S6' => 'bg-dark',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $akreditasi }}</span>
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
                                <td class="text-muted" width="40%">Slot</td>
                                <td>
                                    @if($submission->journalSlot)
                                        Vol. {{ $submission->journalSlot->volume }} No. {{ $submission->journalSlot->issue }} 
                                        ({{ $submission->journalSlot->year }})
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Deadline</td>
                                <td>
                                    @if($submission->journalSlot && $submission->journalSlot->deadline)
                                        {{ \Carbon\Carbon::parse($submission->journalSlot->deadline)->format('d M Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>
                <h6 class="text-muted mb-3"><i class="bi bi-file-text"></i> Data Artikel</h6>

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
                                        <a href="{{ $submission->link_publish }}" target="_blank" class="text-primary">
                                            <i class="bi bi-box-arrow-up-right"></i> {{ $submission->link_publish }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>
                <h6 class="text-muted mb-3"><i class="bi bi-person"></i> Data Penulis</h6>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="40%">Nama Penulis</td>
                                <td><strong>{{ $submission->nama_penulis ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">No HP Penulis</td>
                                <td>{{ $submission->no_hp_penulis ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email Penulis</td>
                                <td>{{ $submission->email_penulis ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($submission->notes)
                <hr>
                <h6 class="text-muted mb-3"><i class="bi bi-chat-left-text"></i> Catatan</h6>
                <div class="alert alert-light">
                    {{ $submission->notes }}
                </div>
                @endif
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('admin.fasttrack.monitoring') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <div>
                    <a href="{{ route('admin.fasttrack.edit', $submission->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <form action="{{ route('admin.fasttrack.destroy', $submission->id) }}" method="POST" class="d-inline" 
                          onsubmit="return confirm('Yakin ingin menghapus submission ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
