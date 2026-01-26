@extends('pic.layouts.app')

@section('title', 'Data Jurnal')
@section('page-title', 'Data Jurnal')

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

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-journal-text"></i> Daftar Jurnal</span>
        <a href="{{ route('pic.journals.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah Jurnal
        </a>
    </div>
    <div class="card-body">
        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('pic.journals.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" 
                           placeholder="🔍 Cari nama jurnal atau publisher..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="akreditasi" class="form-select">
                        <option value="">-- Semua Akreditasi --</option>
                        @foreach($accreditations as $accreditation)
                            <option value="{{ $accreditation->name }}" {{ request('akreditasi') == $accreditation->name ? 'selected' : '' }}>{{ $accreditation->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        <a href="{{ route('pic.journals.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Jurnal</th>
                        <th>Penerbit</th>
                        <th>Kategori</th>
                        <th>Jenis</th>
                        <th>Akreditasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $journal)
                    <tr>
                        <td>{{ $loop->iteration + ($journals->currentPage() - 1) * $journals->perPage() }}</td>
                        <td>
                            <strong>{{ $journal->nama_jurnal }}</strong>
                            @if($journal->link_jurnal)
                                <a href="{{ $journal->link_jurnal }}" target="_blank" class="ms-1" title="Buka Link">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            @endif
                        </td>
                        <td>{{ $journal->publisher ?? '-' }}</td>
                        <td>
                            @if($journal->kategori)
                                <span class="badge bg-{{ $journal->kategori == 'Penelitian' ? 'primary' : 'success' }}">{{ $journal->kategori }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($journal->jenis_jurnal)
                                <span class="badge bg-{{ $journal->jenis_jurnal == 'Jurnal Internasional' ? 'warning' : 'secondary' }}">
                                    {{ $journal->jenis_jurnal == 'Jurnal Internasional' ? 'Intl' : 'Nas' }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($journal->accreditation)
                                <span class="badge bg-info">{{ $journal->accreditation }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($journal->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada data jurnal</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $journals->links() }}
        </div>
    </div>
</div>
@endsection
