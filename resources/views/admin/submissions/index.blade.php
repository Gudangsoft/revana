@extends('layouts.app')

@section('title', 'Data Submit - ' . $appSettings['app_name'])
@section('page-title', 'Data Submit')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text"></i> Data Submit</span>
                <div>
                    <a href="{{ route('admin.submissions.monitoring') }}" class="btn btn-info">
                        <i class="bi bi-bar-chart"></i> Monitoring Proses
                    </a>
                    <a href="{{ route('admin.submissions.create') }}" class="btn btn-primary">
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

                <!-- Filter -->
                <form action="{{ route('admin.submissions.index') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label for="tanggal_dari" class="form-label">Tanggal Dari</label>
                            <input type="date" class="form-control" id="tanggal_dari" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="tanggal_sampai" class="form-label">Tanggal Sampai</label>
                            <input type="date" class="form-control" id="tanggal_sampai" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="journal_master_id" class="form-label">Jurnal</label>
                            <select class="form-select" id="journal_master_id" name="journal_master_id">
                                <option value="">-- Semua Jurnal --</option>
                                @foreach($journals as $journal)
                                    <option value="{{ $journal->id }}" {{ request('journal_master_id') == $journal->id ? 'selected' : '' }}>
                                        {{ $journal->nama_jurnal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">-- Semua Status --</option>
                                @foreach($statusOptions as $key => $value)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.submissions.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Submit</th>
                                <th>ID Artikel</th>
                                <th>Judul Artikel</th>
                                <th>Nama Penulis</th>
                                <th>No HP</th>
                                <th>PIC Marketing</th>
                                <th>Petugas Submit</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $submission)
                            <tr>
                                <td>{{ $loop->iteration + ($submissions->currentPage() - 1) * $submissions->perPage() }}</td>
                                <td><code>{{ $submission->kode_submit }}</code></td>
                                <td>{{ $submission->id_artikel }}</td>
                                <td>
                                    <span title="{{ $submission->judul_artikel }}">{{ Str::limit($submission->judul_artikel, 30) }}</span>
                                    @if($submission->link_artikel)
                                        <a href="{{ $submission->link_artikel }}" target="_blank" class="text-primary">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @endif
                                </td>
                                <td>{{ $submission->nama_penulis }}</td>
                                <td>{{ $submission->no_hp_penulis ?? '-' }}</td>
                                <td>{{ $submission->pic_marketing ?? '-' }}</td>
                                <td>{{ $submission->petugasSubmit?->name ?? '-' }}</td>
                                <td>{{ $submission->tanggal_submit?->format('d/m/Y') }}</td>
                                <td><span class="badge {{ $submission->status_badge_class }}">{{ $submission->status_label }}</span></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.submissions.show', $submission) }}" class="btn btn-sm btn-info" title="Lihat">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.submissions.process', $submission) }}" class="btn btn-sm btn-primary" title="Proses">
                                            <i class="bi bi-gear"></i>
                                        </a>
                                        <a href="{{ route('admin.submissions.edit', $submission) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.submissions.destroy', $submission) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus submission ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada data submission
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $submissions->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
