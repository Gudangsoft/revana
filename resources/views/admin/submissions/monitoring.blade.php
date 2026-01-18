@extends('layouts.app')

@section('title', 'Monitoring Proses - ' . $appSettings['app_name'])
@section('page-title', 'Monitoring Proses Submit')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Total</h6>
                        <h2 class="card-title mb-0">{{ $stats['total'] }}</h2>
                    </div>
                    <i class="bi bi-file-earmark-text fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Submitted</h6>
                        <h2 class="card-title mb-0">{{ $stats['submitted'] }}</h2>
                    </div>
                    <i class="bi bi-clock fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Dalam Proses</h6>
                        <h2 class="card-title mb-0">{{ $stats['in_process'] }}</h2>
                    </div>
                    <i class="bi bi-gear fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-1">Published</h6>
                        <h2 class="card-title mb-0">{{ $stats['published'] }}</h2>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart"></i> Monitoring Proses Submit (Filter Tanggal)</span>
                <a href="{{ route('admin.submissions.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <form action="{{ route('admin.submissions.monitoring') }}" method="GET" class="mb-4">
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
                                <option value="">-- Semua --</option>
                                @foreach($statusOptions as $key => $value)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.submissions.monitoring') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Data Table with Full Process Columns -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-sm" style="font-size: 0.85rem;">
                        <thead class="table-dark">
                            <tr>
                                <th rowspan="2" class="align-middle">Kode Submit</th>
                                <th rowspan="2" class="align-middle">ID Artikel</th>
                                <th rowspan="2" class="align-middle">Judul</th>
                                <th rowspan="2" class="align-middle">Link</th>
                                <th rowspan="2" class="align-middle">Penulis</th>
                                <th rowspan="2" class="align-middle">No HP</th>
                                <th colspan="2" class="text-center">Author Access</th>
                                <th rowspan="2" class="align-middle">PIC Marketing</th>
                                <th rowspan="2" class="align-middle">Petugas Submit</th>
                                <th colspan="3" class="text-center bg-info">Editor 1</th>
                                <th colspan="2" class="text-center bg-warning">Author 1</th>
                                <th colspan="2" class="text-center bg-info">Editor 2</th>
                                <th colspan="4" class="text-center bg-primary">Reviewer 1</th>
                                <th colspan="4" class="text-center bg-primary">Reviewer 2</th>
                                <th colspan="2" class="text-center bg-info">Editor 3</th>
                                <th colspan="2" class="text-center bg-warning">Author 2</th>
                                <th colspan="3" class="text-center bg-success">Production</th>
                            </tr>
                            <tr>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Petugas</th>
                                <th>User/Pass</th>
                                <th>Valid</th>
                                <th>Petugas</th>
                                <th>Valid</th>
                                <th>Petugas</th>
                                <th>Valid</th>
                                <th>Petugas</th>
                                <th>User/Pass</th>
                                <th>Catatan</th>
                                <th>Valid</th>
                                <th>Petugas</th>
                                <th>User/Pass</th>
                                <th>Catatan</th>
                                <th>Valid</th>
                                <th>Petugas</th>
                                <th>Valid</th>
                                <th>Petugas</th>
                                <th>Valid</th>
                                <th>Petugas</th>
                                <th>Link Publish</th>
                                <th>Valid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $s)
                            <tr>
                                <td><code>{{ $s->kode_submit }}</code></td>
                                <td>{{ $s->id_artikel }}</td>
                                <td title="{{ $s->judul_artikel }}">{{ Str::limit($s->judul_artikel, 25) }}</td>
                                <td>
                                    @if($s->link_artikel)
                                        <a href="{{ $s->link_artikel }}" target="_blank"><i class="bi bi-link-45deg"></i></a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ Str::limit($s->nama_penulis, 15) }}</td>
                                <td>{{ $s->no_hp_penulis ?? '-' }}</td>
                                <td><code>{{ $s->username_author ?? '-' }}</code></td>
                                <td><code>{{ $s->password_author ?? '-' }}</code></td>
                                <td>{{ $s->pic_marketing ?? '-' }}</td>
                                <td>{{ $s->petugasSubmit?->name ?? '-' }}</td>
                                
                                <!-- Editor 1 -->
                                <td>{{ $s->petugasEditor1?->name ?? '-' }}</td>
                                <td><small><code>{{ $s->username_editor ?? '-' }}</code>/<code>{{ $s->password_editor ?? '-' }}</code></small></td>
                                <td class="text-center">{!! $s->editor1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Author 1 -->
                                <td>{{ $s->petugasAuthor1?->name ?? '-' }}</td>
                                <td class="text-center">{!! $s->author1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Editor 2 -->
                                <td>{{ $s->petugasEditor2?->name ?? '-' }}</td>
                                <td class="text-center">{!! $s->editor2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Reviewer 1 -->
                                <td>{{ $s->petugasReviewer1?->name ?? '-' }}</td>
                                <td><small><code>{{ $s->username_reviewer1 ?? '-' }}</code>/<code>{{ $s->password_reviewer1 ?? '-' }}</code></small></td>
                                <td title="{{ $s->catatan_reviewer1 }}">{{ Str::limit($s->catatan_reviewer1, 15) ?? '-' }}</td>
                                <td class="text-center">{!! $s->reviewer1_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Reviewer 2 -->
                                <td>{{ $s->petugasReviewer2?->name ?? '-' }}</td>
                                <td><small><code>{{ $s->username_reviewer2 ?? '-' }}</code>/<code>{{ $s->password_reviewer2 ?? '-' }}</code></small></td>
                                <td title="{{ $s->catatan_reviewer2 }}">{{ Str::limit($s->catatan_reviewer2, 15) ?? '-' }}</td>
                                <td class="text-center">{!! $s->reviewer2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Editor 3 -->
                                <td>{{ $s->petugasEditor3?->name ?? '-' }}</td>
                                <td class="text-center">{!! $s->editor3_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Author 2 -->
                                <td>{{ $s->petugasAuthor2?->name ?? '-' }}</td>
                                <td class="text-center">{!! $s->author2_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                                
                                <!-- Production -->
                                <td>{{ $s->petugasProduction?->name ?? '-' }}</td>
                                <td>
                                    @if($s->link_publish)
                                        <a href="{{ $s->link_publish }}" target="_blank"><i class="bi bi-link-45deg"></i></a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">{!! $s->production_valid ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>' !!}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="30" class="text-center text-muted py-4">
                                    Tidak ada data
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
