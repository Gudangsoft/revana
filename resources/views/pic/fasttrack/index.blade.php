@extends('pic.layouts.app')

@section('title', 'Data Fasttrack')
@section('page-title', 'Data Fasttrack')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-lightning-charge text-warning"></i> Data Submission Fasttrack</span>
                <a href="{{ route('pic.fasttrack.create') }}" class="btn btn-warning">
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

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Info Box -->
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-lightning-charge"></i> <strong>Fasttrack</strong> adalah proses cepat untuk artikel yang sudah publish. 
                    Input langsung dengan link publish, tanpa melalui proses workflow normal.
                </div>

                <!-- Search -->
                <form action="{{ route('pic.fasttrack.index') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Cari kode/judul/penulis..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">-- Status --</option>
                                <option value="PUBLISHED" {{ request('status') == 'PUBLISHED' ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            @if(request()->hasAny(['search', 'status']))
                            <a href="{{ route('pic.fasttrack.index') }}" class="btn btn-outline-secondary">
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
                                    <br><small class="text-muted">
                                        <span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge"></i> Fasttrack</span>
                                    </small>
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
                                    <a href="{{ route('pic.fasttrack.create') }}" class="btn btn-warning">
                                        <i class="bi bi-plus-circle"></i> Input Fasttrack Pertama
                                    </a>
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
