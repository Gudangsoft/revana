@extends('pic.layouts.app')

@section('title', 'Data Submit' . (request('program') ? ' ' . strtoupper(request('program')) : ' Reguler'))
@section('page-title', 'Data Submit' . (request('program') ? ' ' . strtoupper(request('program')) : ' Reguler'))

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
@include('partials.auto-refresh', ['interval' => 30, 'arId' => 'pic-sub'])

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
            <input type="hidden" name="program" value="{{ request('program') }}">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Cari kode/judul/penulis..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="akreditasi" class="form-select">
                    <option value="">Semua Akreditasi</option>
                    @foreach($accreditations as $accreditation)
                        <option value="{{ $accreditation->name }}" {{ request('akreditasi') == $accreditation->name ? 'selected' : '' }}>
                            {{ $accreditation->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="jenis" class="form-select">
                    <option value="">Semua Jenis</option>
                    @foreach($jenisJurnals as $jenis)
                        <option value="{{ $jenis->name }}" {{ request('jenis') == $jenis->name ? 'selected' : '' }}>
                            {{ $jenis->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
            <div class="col-md-3">
                <a href="{{ route('pic.submissions.index') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-earmark-plus"></i> Daftar Submit{{ request('program') ? ' ' . strtoupper(request('program')) : ' Reguler' }}</span>
        <div class="d-flex align-items-center gap-2">
            @include('partials.column-toggle', ['tableId' => 'picSubmissionsTable', 'columns' => ['Kode Submit', 'Judul', 'Jurnal', 'Penulis', 'No HP', 'Akreditasi', 'Jenis', 'Link Submit', 'Tanggal', 'Aksi'], 'columnOffset' => 1])
            <a href="{{ route('pic.submissions.create', array_filter(['program' => request('program')])) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Tambah Submission
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="picSubmissionsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode Submit</th>
                        <th>Judul</th>
                        <th>Jurnal</th>
                        <th>Penulis</th>
                        <th>No HP</th>
                        <th>Akreditasi</th>
                        <th>Jenis</th>
                        <th>Link Submit</th>
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
                            @if($submission->journalSlot)
                                <br><small class="text-muted" style="font-size: 0.65rem; line-height: 1.2;" title="{{ $submission->journalSlot->journalMaster?->nama_jurnal ?? '-' }} - Vol.{{ $submission->journalSlot->volume }} No.{{ $submission->journalSlot->nomor }}">{{ Str::limit($submission->journalSlot->journalMaster?->nama_jurnal ?? '-', 20) }}<br>Vol.{{ $submission->journalSlot->volume }} No.{{ $submission->journalSlot->nomor }}</small>
                            @endif
                            @if($submission->process_type === 'fasttrack')
                                <br><span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge"></i> FT</span>
                            @endif
                        </td>
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
                            @if($submission->no_hp_penulis)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $submission->no_hp_penulis) }}" target="_blank" class="text-decoration-none" title="Chat WhatsApp">
                                    <i class="bi bi-whatsapp text-success"></i> {{ $submission->no_hp_penulis }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($submission->journalSlot && $submission->journalSlot->journalMaster)
                                <span class="badge bg-primary">{{ $submission->journalSlot->journalMaster->accreditation ?? '-' }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($submission->journalSlot && $submission->journalSlot->journalMaster)
                                <span class="badge bg-info">{{ $submission->journalSlot->journalMaster->jenis_jurnal ?? '-' }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($submission->link_artikel)
                                <a href="{{ $submission->link_artikel }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Buka Link Submit">
                                    <i class="bi bi-link-45deg"></i>
                                </a>
                            @else
                                -
                            @endif
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
                        <td colspan="11" class="text-center text-muted">Belum ada data submission</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @include('partials.per-page-selector', ['paginator' => $submissions])
    </div>
</div>
@endsection
