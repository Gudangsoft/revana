@extends('pic.layouts.app')

@section('title', 'Monitoring Fasttrack')
@section('page-title', 'Monitoring Fasttrack')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $totalFasttrack }}</h3>
                        <small>Total Fasttrack</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $thisMonthFasttrack }}</h3>
                        <small>Bulan Ini</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart text-warning"></i> Monitoring Submission Fasttrack</span>
                <a href="{{ route('pic.fasttrack.create') }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-plus-circle"></i> Input Fasttrack
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Filter -->
                <form action="{{ route('pic.fasttrack.monitoring') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" placeholder="Cari kode/judul/penulis..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="journal_master_id">
                                <option value="">-- Jurnal --</option>
                                @foreach($journals as $journal)
                                    <option value="{{ $journal->id }}" {{ request('journal_master_id') == $journal->id ? 'selected' : '' }}>
                                        {{ Str::limit($journal->nama_jurnal, 30) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}" placeholder="Dari Tanggal">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}" placeholder="Sampai Tanggal">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            @if(request()->hasAny(['search', 'journal_master_id', 'from_date', 'to_date']))
                            <a href="{{ route('pic.fasttrack.monitoring') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Submit</th>
                                <th>Jurnal</th>
                                <th>Judul Artikel</th>
                                <th>Penulis</th>
                                <th>Marketing</th>
                                <th>Link Publish</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $submission)
                            <tr>
                                <td>{{ $loop->iteration + ($submissions->currentPage() - 1) * $submissions->perPage() }}</td>
                                <td>
                                    <code class="text-warning">{{ $submission->kode_submit }}</code>
                                    <br><span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge"></i> Fasttrack</span>
                                </td>
                                <td>
                                    @if($submission->journalSlot && $submission->journalSlot->journalMaster)
                                        {{ Str::limit($submission->journalSlot->journalMaster->nama_jurnal, 20) }}
                                        <br><small class="text-muted">
                                            {{ $submission->journalSlot->journalMaster->accreditation ?? '-' }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ Str::limit($submission->judul_artikel, 25) }}</td>
                                <td>{{ Str::limit($submission->nama_penulis, 15) }}</td>
                                <td>
                                    @if($submission->marketing)
                                        {{ $submission->marketing->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($submission->link_publish)
                                        <a href="{{ $submission->link_publish }}" target="_blank" class="btn btn-sm btn-success">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $submission->tanggal_submit ? $submission->tanggal_submit->format('d/m/Y') : '-' }}</td>
                                <td>
                                    <a href="{{ route('pic.fasttrack.show', $submission) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-6"></i>
                                    <p class="mt-2">Belum ada data fasttrack</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $submissions->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
