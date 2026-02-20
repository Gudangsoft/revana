@extends('pic.layouts.app')

@section('title', 'Data Submit Fasttrack')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-warning">
                <span><i class="bi bi-lightning-charge text-dark"></i> <strong>Data Submit Fasttrack</strong></span>
                <a href="{{ route('pic.fasttrack.create') }}" class="btn btn-dark btn-sm">
                    <i class="bi bi-plus-circle"></i> Input Fasttrack
                </a>
            </div>
            <div class="card-body">
                <!-- Summary Cards -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body text-center p-3">
                                <h6 class="card-title mb-1 small">Total Fasttrack</h6>
                                <h3 class="mb-0">{{ $submissions->total() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center p-3">
                                <h6 class="card-title mb-1 small">Published</h6>
                                <h3 class="mb-0">{{ $submissions->where('status', 'PUBLISHED')->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center p-3">
                                <h6 class="card-title mb-1 small">Sedang Proses</h6>
                                <h3 class="mb-0">{{ $submissions->where('status', '!=', 'PUBLISHED')->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center p-3">
                                <h6 class="card-title mb-1 small">Total Data</h6>
                                <h3 class="mb-0">{{ $submissions->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <form action="{{ route('pic.fasttrack.index') }}" method="GET" class="mb-3">
                    <div class="row g-2">
                        <div class="col-md-2">
                            <label class="form-label small">Tanggal Dari</label>
                            <input type="date" name="tanggal_dari" class="form-control form-control-sm" value="{{ request('tanggal_dari') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Tanggal Sampai</label>
                            <input type="date" name="tanggal_sampai" class="form-control form-control-sm" value="{{ request('tanggal_sampai') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Cari</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Kode/Judul/Penulis..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                                <a href="{{ route('pic.fasttrack.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-circle"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Kode Submit</th>
                                <th>Judul</th>
                                <th>Jurnal</th>
                                <th>Penulis</th>
                                <th>No HP</th>
                                <th>Akreditasi</th>
                                <th>Jenis</th>
                                <th>Link Submit</th>
                                <th>Tanggal</th>
                                <th style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $s)
                            <tr>
                                <td>{{ $loop->iteration + ($submissions->currentPage() - 1) * $submissions->perPage() }}</td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-lightning-charge"></i> {{ $s->kode_submit }}
                                    </span>
                                </td>
                                <td title="{{ $s->judul_artikel }}">{{ Str::limit($s->judul_artikel, 30) }}</td>
                                <td>
                                    @if($s->journalSlot && $s->journalSlot->journalMaster)
                                        <strong>{{ Str::limit($s->journalSlot->journalMaster->nama_jurnal, 25) }}</strong>
                                        @if($s->journalSlot->journalMaster->publisher)
                                            <br><small class="text-muted"><i class="bi bi-building"></i> {{ Str::limit($s->journalSlot->journalMaster->publisher, 20) }}</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ Str::limit($s->nama_penulis, 20) }}</td>
                                <td>
                                    @if($s->no_hp_penulis)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $s->no_hp_penulis) }}" target="_blank" class="text-decoration-none" title="Chat WhatsApp">
                                            <i class="bi bi-whatsapp text-success"></i> {{ $s->no_hp_penulis }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($s->journalSlot && $s->journalSlot->journalMaster)
                                        <span class="badge bg-primary">{{ $s->journalSlot->journalMaster->accreditation ?? '-' }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($s->journalSlot && $s->journalSlot->journalMaster)
                                        <span class="badge bg-info">{{ $s->journalSlot->journalMaster->jenis_jurnal ?? '-' }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($s->link_artikel)
                                        <a href="{{ $s->link_artikel }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Buka Link Submit">
                                            <i class="bi bi-link-45deg"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $s->tanggal_submit ? \Carbon\Carbon::parse($s->tanggal_submit)->format('d/m/Y') : $s->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('pic.fasttrack.show', $s) }}" class="btn btn-info btn-sm" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0 mt-2">Belum ada data fasttrack submission</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @include('partials.per-page-selector', ['paginator' => $submissions])
            </div>
        </div>
    </div>
</div>
@endsection
