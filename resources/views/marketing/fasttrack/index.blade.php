@extends('marketing.layouts.app')

@section('title', 'Data Fasttrack')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-lightning-charge text-warning"></i> Fasttrack Saya
    </h4>
    <div>
        <a href="{{ route('marketing.fasttrack.create') }}" class="btn btn-warning">
            <i class="bi bi-plus-circle"></i> Input Fasttrack
        </a>
        <span class="badge bg-warning text-dark fs-6 ms-2">Total: {{ $submissions->total() }} artikel</span>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Info Box -->
<div class="alert alert-warning mb-4">
    <i class="bi bi-lightning-charge"></i> <strong>Fasttrack</strong> adalah proses cepat untuk artikel yang sudah publish. 
    Input langsung dengan link publish, tanpa melalui proses workflow normal.
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" 
                       placeholder="Cari kode/judul/penulis..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search"></i> Cari
                </button>
                @if(request()->hasAny(['search']))
                <a href="{{ route('marketing.fasttrack.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Kode Submit</th>
                        <th>Jurnal</th>
                        <th>Judul Artikel</th>
                        <th>Penulis</th>
                        <th>Link Publish</th>
                        <th>Tanggal</th>
                        <th>Status</th>
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
                                {{ Str::limit($submission->journalSlot->journalMaster->nama_jurnal, 25) }}
                                <br><small class="text-muted">
                                    Vol. {{ $submission->journalSlot->volume ?? '-' }} No. {{ $submission->journalSlot->nomor ?? '-' }}
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ Str::limit($submission->judul_artikel, 30) }}</td>
                        <td>{{ Str::limit($submission->nama_penulis, 20) }}</td>
                        <td>
                            @if($submission->link_publish)
                                <a href="{{ $submission->link_publish }}" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-box-arrow-up-right"></i> Link
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $submission->tanggal_submit ? $submission->tanggal_submit->format('d/m/Y') : '-' }}</td>
                        <td>
                            <span class="badge bg-success">Published</span>
                        </td>
                        <td>
                            <a href="{{ route('marketing.fasttrack.show', $submission) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-inbox display-6"></i>
                            <p class="mt-2">Belum ada data fasttrack</p>
                            <a href="{{ route('marketing.fasttrack.create') }}" class="btn btn-warning">
                                <i class="bi bi-plus-circle"></i> Input Fasttrack Pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $submissions->withQueryString()->links() }}
</div>
@endsection
