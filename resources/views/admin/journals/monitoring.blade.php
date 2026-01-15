@extends('layouts.app')

@section('title', ' - Pemantauan Slot Artikel')
@section('page-title', 'Pemantauan Slot Artikel Jurnal')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bar-chart"></i> Pemantauan Slot Artikel Jurnal</h2>
        <div>
            <a href="{{ route('admin.journals.index') }}" class="btn btn-secondary">
                <i class="bi bi-list"></i> Daftar Jurnal
            </a>
            <a href="{{ route('admin.journals.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Jurnal
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border-radius: 15px;">
                <div class="card-body text-white py-4">
                    <h1 class="display-3 fw-bold mb-2">{{ $stats['total_journals'] }}</h1>
                    <p class="mb-0 fs-5 fw-normal">Total Jurnal</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 15px;">
                <div class="card-body text-white py-4">
                    <h1 class="display-3 fw-bold mb-2">{{ $stats['total_slots'] }}</h1>
                    <p class="mb-0 fs-5 fw-normal">Total Slot</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); border-radius: 15px;">
                <div class="card-body text-white py-4">
                    <h1 class="display-3 fw-bold mb-2">{{ $stats['slots_used'] }}</h1>
                    <p class="mb-0 fs-5 fw-normal">Slot Terpakai</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 15px;">
                <div class="card-body text-white py-4">
                    <h1 class="display-3 fw-bold mb-2">{{ $stats['slots_available'] }}</h1>
                    <p class="mb-0 fs-5 fw-normal">Slot Tersedia</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Journals Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="journalMonitoringTable">
                    <thead class="table-light">
                        <tr>
                            <th width="3%">No</th>
                            <th width="20%">Nama Jurnal</th>
                            <th width="12%">Volume</th>
                            <th width="10%">Akreditasi</th>
                            <th width="8%">Total Slot</th>
                            <th width="8%">Terpakai</th>
                            <th width="8%">Tersedia</th>
                            <th width="18%">Progress</th>
                            <th width="8%">Status</th>
                            <th width="5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journalStats as $index => $stat)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ Str::limit($stat['journal']->title, 50) }}</strong><br>
                                <small class="text-muted">
                                    <i class="bi bi-person"></i> {{ $stat['journal']->creator->name }}
                                </small>
                            </td>
                            <td>
                                @if($stat['journal']->volume)
                                    <span class="badge bg-secondary">{{ $stat['journal']->volume }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $stat['journal']->accreditation }}</span>
                            </td>
                            <td class="text-center">
                                <strong class="fs-5">{{ $stat['total_slots'] }}</strong>
                            </td>
                            <td class="text-center">
                                <strong class="fs-5 text-primary">{{ $stat['used_slots'] }}</strong>
                            </td>
                            <td class="text-center">
                                <strong class="fs-5 text-success">{{ $stat['available_slots'] }}</strong>
                            </td>
                            <td>
                                <div class="mb-1">
                                    <small><strong>{{ $stat['percentage'] }}%</strong></small>
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $stat['status'] == 'danger' ? 'bg-danger' : ($stat['status'] == 'warning' ? 'bg-warning' : 'bg-success') }}" 
                                         role="progressbar" 
                                         style="width: {{ $stat['percentage'] }}%"
                                         aria-valuenow="{{ $stat['percentage'] }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ $stat['percentage'] }}%
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($stat['status'] == 'danger')
                                    <span class="badge bg-danger">
                                        <i class="bi bi-exclamation-triangle"></i> Penuh
                                    </span>
                                @elseif($stat['status'] == 'warning')
                                    <span class="badge bg-warning">
                                        <i class="bi bi-exclamation-circle"></i> Hampir Penuh
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Tersedia
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.journals.edit', $stat['journal']) }}" 
                                   class="btn btn-sm btn-warning" 
                                   title="Edit Jurnal">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2">Belum ada data jurnal</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .table td {
        vertical-align: middle;
    }
    .progress {
        background-color: #e9ecef;
    }
    .accordion-button:not(.collapsed) {
        background-color: transparent;
        box-shadow: none;
    }
    .accordion-button::after {
        filter: brightness(0) invert(1);
    }
</style>

<script>
    $(document).ready(function() {
        $('#journalMonitoringTable').DataTable({
            "pageLength": 25,
            "order": [[7, "desc"]], // Sort by percentage
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
            }
        });
    });
</script>
@endsection
