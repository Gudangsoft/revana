@extends('pic.layouts.app')

@section('title', 'Data Submission')
@section('page-title', 'Data Submission')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari kode/judul/penulis..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('pic.submissions.index') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-earmark-plus"></i> Daftar Submission</span>
        <a href="{{ route('pic.submissions.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah Submission
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode Submit</th>
                        <th>ID Artikel</th>
                        <th>Judul</th>
                        <th>Jurnal</th>
                        <th>Penulis</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $submission)
                    <tr>
                        <td>{{ $loop->iteration + ($submissions->currentPage() - 1) * $submissions->perPage() }}</td>
                        <td>
                            <code>{{ $submission->kode_submit }}</code>
                            @if($submission->process_type === 'fasttrack')
                                <span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge"></i> FT</span>
                            @endif
                        </td>
                        <td>{{ $submission->id_artikel ?? '-' }}</td>
                        <td title="{{ $submission->judul_artikel }}">{{ Str::limit($submission->judul_artikel, 30) }}</td>
                        <td>
                            @if($submission->journalSlot && $submission->journalSlot->journalMaster)
                                <strong>{{ Str::limit($submission->journalSlot->journalMaster->nama_jurnal, 25) }}</strong>
                                @if($submission->journalSlot->journalMaster->publisher)
                                    <br><small class="text-muted"><i class="bi bi-building"></i> {{ Str::limit($submission->journalSlot->journalMaster->publisher, 20) }}</small>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ Str::limit($submission->nama_penulis, 20) }}</td>
                        <td>
                            @php
                                $statusColors = [
                                    'new' => 'secondary',
                                    'in_progress' => 'info',
                                    'published' => 'success',
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$submission->status] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                            </span>
                        </td>
                        <td>{{ $submission->tanggal_submit ? \Carbon\Carbon::parse($submission->tanggal_submit)->format('d/m/Y') : $submission->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('pic.submissions.show', $submission) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">Belum ada data submission</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $submissions->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
