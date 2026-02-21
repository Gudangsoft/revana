@extends('pic.layouts.app')

@section('title', 'Data Jurnal')
@section('page-title', 'Data Jurnal')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text"></i> Daftar Jurnal</span>
                <div class="d-flex align-items-center gap-2">
                    @include('partials.column-toggle', ['tableId' => 'picJournalsTable', 'columns' => ['Nama Jurnal', 'Kode Jurnal', 'Publisher', 'Kategori', 'Jenis', 'Akreditasi', 'Status', 'Aksi'], 'columnOffset' => 1])
                    <a href="{{ route('pic.journal-slots.monitoring') }}" class="btn btn-info">
                        <i class="bi bi-bar-chart"></i> Pemantauan Slot
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

                <!-- Search & Filter Form -->
                <form action="{{ route('pic.journals.index') }}" method="GET" class="mb-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="🔍 Cari nama jurnal..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="akreditasi">
                                <option value="">-- Akreditasi --</option>
                                <option value="SINTA 1" {{ request('akreditasi') == 'SINTA 1' ? 'selected' : '' }}>SINTA 1</option>
                                <option value="SINTA 2" {{ request('akreditasi') == 'SINTA 2' ? 'selected' : '' }}>SINTA 2</option>
                                <option value="SINTA 3" {{ request('akreditasi') == 'SINTA 3' ? 'selected' : '' }}>SINTA 3</option>
                                <option value="SINTA 4" {{ request('akreditasi') == 'SINTA 4' ? 'selected' : '' }}>SINTA 4</option>
                                <option value="SINTA 5" {{ request('akreditasi') == 'SINTA 5' ? 'selected' : '' }}>SINTA 5</option>
                                <option value="SINTA 6" {{ request('akreditasi') == 'SINTA 6' ? 'selected' : '' }}>SINTA 6</option>
                                <option value="Non SINTA" {{ request('akreditasi') == 'Non SINTA' ? 'selected' : '' }}>Non SINTA</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="kategori">
                                <option value="">-- Kategori --</option>
                                <option value="Penelitian" {{ request('kategori') == 'Penelitian' ? 'selected' : '' }}>Penelitian</option>
                                <option value="PKM" {{ request('kategori') == 'PKM' ? 'selected' : '' }}>PKM</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="jenis">
                                <option value="">-- Jenis --</option>
                                <option value="Jurnal Nasional" {{ request('jenis') == 'Jurnal Nasional' ? 'selected' : '' }}>Nasional</option>
                                <option value="Jurnal Internasional" {{ request('jenis') == 'Jurnal Internasional' ? 'selected' : '' }}>Internasional</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Cari
                            </button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('pic.journals.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                        @if(request()->hasAny(['search', 'akreditasi', 'kategori', 'jenis']))
                        <div class="col-auto">
                            <span class="badge bg-info py-2 px-3">
                                <i class="bi bi-funnel"></i> {{ collect(request()->only(['search', 'akreditasi', 'kategori', 'jenis']))->filter()->count() }} filter aktif
                            </span>
                        </div>
                        @endif
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover" id="picJournalsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Jurnal</th>
                                <th>Kode Jurnal</th>
                                <th>Publisher</th>
                                <th>Kategori</th>
                                <th>Jenis</th>
                                <th>Akreditasi</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journals as $journal)
                            <tr>
                                <td>{{ $loop->iteration + ($journals->currentPage() - 1) * $journals->perPage() }}</td>
                                <td>
                                    <strong>{{ $journal->nama_jurnal }}</strong>
                                    @if($journal->link_jurnal)
                                        <br><small class="text-muted">
                                            <a href="{{ $journal->link_jurnal }}" target="_blank" class="text-decoration-none">
                                                <i class="bi bi-link-45deg"></i> Link Jurnal
                                            </a>
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <code class="badge bg-light text-dark">{{ $journal->kode_jurnal }}</code>
                                </td>
                                <td>{{ $journal->publisher ?? '-' }}</td>
                                <td>
                                    @if($journal->kategori)
                                        <span class="badge bg-info">{{ $journal->kategori }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($journal->jenis_jurnal)
                                        <span class="badge bg-primary">{{ $journal->jenis_jurnal }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($journal->accreditation)
                                        <span class="badge bg-success">{{ $journal->accreditation }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($journal->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($journal->link_jurnal)
                                        <a href="{{ $journal->link_jurnal }}" target="_blank" class="btn btn-sm btn-info" title="Lihat Jurnal">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Belum ada data jurnal</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('partials.per-page-selector', ['paginator' => $journals, 'default' => 20])
            </div>
        </div>
    </div>
</div>
@endsection