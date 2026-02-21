@extends('layouts.app')

@section('title', 'Data Submit FS - ' . $appSettings['app_name'])
@section('page-title', 'Data Submit FS')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text"></i> Data Submit Fasttrack</span>
                <div class="btn-group">
                    <a href="{{ route('admin.submissions.export', request()->query()) }}" class="btn btn-info">
                        <i class="bi bi-download"></i> Export
                    </a>
                    <a href="{{ route('admin.fasttrack.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Submit
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

                <div class="alert alert-warning">
                    <i class="bi bi-lightning-charge"></i> <strong>Submit Fasttrack:</strong> Data submissions dengan proses fasttrack - artikel sudah publish langsung.
                </div>

                <!-- Search & Filter Form -->
                <form action="{{ route('admin.fasttrack-management.submissions.index') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" placeholder="Cari kode submit, judul, penulis..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="journal_master_id">
                                <option value="">-- Semua Jurnal --</option>
                                @foreach($journals as $journal)
                                    <option value="{{ $journal->id }}" {{ request('journal_master_id') == $journal->id ? 'selected' : '' }}>
                                        {{ $journal->nama_jurnal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="tanggal_dari" value="{{ request('tanggal_dari') }}" placeholder="Dari">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" placeholder="Sampai">
                        </div>
                        <div class="col-md-2">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                                <a href="{{ route('admin.fasttrack-management.submissions.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Kode Submit</th>
                                <th>Artikel</th>
                                <th>Jurnal/Slot</th>
                                <th>Tgl Submit</th>
                                <th>Status</th>
                                <th>Marketing</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $submission)
                            <tr>
                                <td>
                                    <code>{{ $submission->kode_submit }}</code>
                                    <br><small class="text-success">Fasttrack</small>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ Str::limit($submission->judul_artikel, 40) }}</strong>
                                        <br><small class="text-muted">{{ $submission->nama_penulis }}</small>
                                        @if($submission->link_publish)
                                            <br><a href="{{ $submission->link_publish }}" target="_blank" class="badge bg-success text-decoration-none">
                                                <i class="bi bi-link-45deg"></i> Published
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($submission->journalSlot)
                                        <div>
                                            <strong>{{ $submission->journalSlot->journalMaster->nama_jurnal }}</strong>
                                            <br><small class="text-muted">
                                                Vol. {{ $submission->journalSlot->volume }} No. {{ $submission->journalSlot->nomor }} - {{ $submission->journalSlot->tahun }}
                                            </small>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $submission->tanggal_submit ? $submission->tanggal_submit->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($submission->status) {
                                            'PUBLISHED' => 'bg-success',
                                            'REJECTED' => 'bg-danger',
                                            default => 'bg-warning'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $submission->status }}</span>
                                </td>
                                <td>
                                    @if($submission->marketing)
                                        <span class="badge bg-info">{{ $submission->marketing->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.submissions.show', $submission) }}" class="btn btn-outline-info" title="Lihat">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.submissions.edit', $submission) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($submission->link_publish)
                                            <a href="{{ $submission->link_publish }}" target="_blank" class="btn btn-outline-success" title="Lihat Publikasi">
                                                <i class="bi bi-link-45deg"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Tidak ada data submit fasttrack yang ditemukan
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @include('partials.per-page-selector', ['paginator' => $submissions, 'default' => 20])
            </div>
        </div>
    </div>
</div>
@endsection