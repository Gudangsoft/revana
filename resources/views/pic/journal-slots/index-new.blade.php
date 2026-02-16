@extends('pic.layouts.app')

@section('title', 'Data Slot')
@section('page-title', 'Data Slot')

@section('sidebar')
    @include('pic.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar3"></i> Data Slot & Monitoring</span>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-3" id="slotTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ request('tab') != 'monitoring' ? 'active' : '' }}" id="data-tab" data-bs-toggle="tab" data-bs-target="#data-panel" type="button" role="tab">
                            <i class="bi bi-table"></i> Data Slot
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ request('tab') == 'monitoring' ? 'active' : '' }}" id="monitoring-tab" data-bs-toggle="tab" data-bs-target="#monitoring-panel" type="button" role="tab">
                            <i class="bi bi-bar-chart"></i> Monitoring Slot
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="slotTabsContent">
                    <!-- Data Slot Tab -->
                    <div class="tab-pane fade {{ request('tab') != 'monitoring' ? 'show active' : '' }}" id="data-panel" role="tabpanel">
                        <!-- Search & Filter Form -->
                        <form action="{{ route('pic.journal-slots.index') }}" method="GET" class="mb-4">
                            <input type="hidden" name="tab" value="data">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="search" placeholder="Cari jurnal / kode slot..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="bulan">
                                        <option value="">-- Bulan --</option>
                                        @foreach($bulanOptions as $key => $value)
                                            <option value="{{ $key }}" {{ request('bulan') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="tahun">
                                        <option value="">-- Tahun --</option>
                                        @for($y = date('Y') + 1; $y >= 2020; $y--)
                                            <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="status">
                                        <option value="">-- Status --</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3 mt-1">
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
                                        <option value="Jurnal Nasional" {{ request('jenis') == 'Jurnal Nasional' ? 'selected' : '' }}>Jurnal Nasional</option>
                                        <option value="Jurnal Internasional" {{ request('jenis') == 'Jurnal Internasional' ? 'selected' : '' }}>Jurnal Internasional</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="akreditasi">
                                        <option value="">-- Akreditasi --</option>
                                        @foreach($accreditations as $accreditation)
                                            <option value="{{ $accreditation->name }}" {{ request('akreditasi') == $accreditation->name ? 'selected' : '' }}>{{ $accreditation->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="bi bi-search"></i> Cari
                                    </button>
                                    @if(request()->hasAny(['search', 'bulan', 'tahun', 'status', 'kategori', 'jenis', 'akreditasi']))
                                    <a href="{{ route('pic.journal-slots.index') }}" class="btn btn-outline-secondary">
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
                                        <th>Kode Slot</th>
                                        <th>Nama Jurnal</th>
                                        <th>Publisher</th>
                                        <th>Kategori</th>
                                        <th>Jenis</th>
                                        <th>Akreditasi</th>
                                        <th>Volume</th>
                                        <th>Nomor</th>
                                        <th>Bulan</th>
                                        <th>Tahun</th>
                                        <th>Jumlah Slot</th>
                                        <th>Terpakai</th>
                                        <th>Tersedia</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($slots as $slot)
                                    <tr>
                                        <td>{{ $loop->iteration + ($slots->currentPage() - 1) * $slots->perPage() }}</td>
                                        <td>
                                            <x-slot-link :slot="$slot" guard="pic" />
                                        </td>
                                        <td>{{ Str::limit($slot->journalMaster->nama_jurnal, 30) }}</td>
                                        <td>{{ Str::limit($slot->journalMaster->publisher, 20) }}</td>
                                        <td>
                                            @if($slot->journalMaster->kategori)
                                                <span class="badge bg-{{ $slot->journalMaster->kategori == 'Penelitian' ? 'primary' : 'success' }}">{{ $slot->journalMaster->kategori }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($slot->journalMaster->jenis_jurnal)
                                                <span class="badge bg-{{ $slot->journalMaster->jenis_jurnal == 'Jurnal Internasional' ? 'warning' : 'secondary' }}">
                                                    {{ $slot->journalMaster->jenis_jurnal == 'Jurnal Internasional' ? 'Internasional' : 'Nasional' }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($slot->journalMaster->accreditation)
                                                <span class="badge bg-info">{{ $slot->journalMaster->accreditation }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $slot->volume }}</td>
                                        <td>{{ $slot->nomor }}</td>
                                        <td>{{ $slot->bulan }}</td>
                                        <td>{{ $slot->tahun }}</td>
                                        <td><span class="badge bg-secondary">{{ $slot->jumlah_slot }}</span></td>
                                        <td><span class="badge bg-warning">{{ $slot->slot_terpakai }}</span></td>
                                        <td><span class="badge bg-success">{{ $slot->slot_tersedia }}</span></td>
                                        <td>
                                            @if($slot->is_full)
                                                <span class="badge bg-danger">Penuh</span>
                                            @elseif($slot->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($slot->journalMaster && $slot->journalMaster->link_jurnal)
                                                <a href="{{ $slot->journalMaster->link_jurnal }}" target="_blank" class="btn btn-sm btn-info" title="Lihat Jurnal">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="16" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            Belum ada data slot
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $slots->appends(request()->except('page'))->links() }}
                        </div>
                    </div>
                    <!-- End Data Slot Tab -->

                    <!-- Monitoring Tab -->
                    <div class="tab-pane fade {{ request('tab') == 'monitoring' ? 'show active' : '' }}" id="monitoring-panel" role="tabpanel">
                        <p class="text-muted">
                            <i class="bi bi-info-circle"></i> Monitoring slot jurnal akan ditampilkan di sini
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
