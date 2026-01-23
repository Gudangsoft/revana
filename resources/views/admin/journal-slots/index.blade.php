@extends('layouts.app')

@section('title', 'Data Slot - ' . $appSettings['app_name'])
@section('page-title', 'Data Slot')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar3"></i> Data Slot</span>
                <div class="btn-group">
                    <a href="{{ route('admin.journal-slots.template') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-file-earmark-arrow-down"></i> Template
                    </a>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-upload"></i> Import
                    </button>
                    <a href="{{ route('admin.journal-slots.export', request()->query()) }}" class="btn btn-info">
                        <i class="bi bi-download"></i> Export
                    </a>
                    <a href="{{ route('admin.journal-slots.monitoring') }}" class="btn btn-warning">
                        <i class="bi bi-bar-chart"></i> Monitoring
                    </a>
                    <a href="{{ route('admin.journal-slots.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah
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
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Search & Filter Form -->
                <form action="{{ route('admin.journal-slots.index') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" placeholder="Cari jurnal / kode slot..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="bulan" name="bulan">
                                <option value="">-- Bulan --</option>
                                @foreach($bulanOptions as $key => $value)
                                    <option value="{{ $key }}" {{ request('bulan') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="tahun" name="tahun">
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
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            @if(request()->hasAny(['search', 'bulan', 'tahun', 'status']))
                            <a href="{{ route('admin.journal-slots.index') }}" class="btn btn-outline-secondary">
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
                                <th>Volume</th>
                                <th>Nomor</th>
                                <th>Bulan</th>
                                <th>Tahun</th>
                                <th>Jumlah Slot</th>
                                <th>Terpakai</th>
                                <th>Tersedia</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slots as $slot)
                            <tr>
                                <td>{{ $loop->iteration + ($slots->currentPage() - 1) * $slots->perPage() }}</td>
                                <td><code>{{ $slot->kode_slot }}</code></td>
                                <td>
                                    <a href="{{ route('admin.journal-masters.show', $slot->journalMaster) }}">
                                        {{ Str::limit($slot->journalMaster->nama_jurnal, 30) }}
                                    </a>
                                </td>
                                <td>{{ Str::limit($slot->journalMaster->publisher, 20) }}</td>
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
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.journal-slots.show', $slot) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.journal-slots.edit', $slot) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.journal-slots.toggle-active', $slot) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $slot->is_active ? 'btn-secondary' : 'btn-success' }}" title="{{ $slot->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="bi {{ $slot->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.journal-slots.destroy', $slot) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus slot ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada data slot
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    @include('components.simple-pagination', ['paginator' => $slots->withQueryString()])
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel"><i class="bi bi-upload"></i> Import Data Slot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.journal-slots.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">File Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">Format: xlsx, xls, csv. Maksimal 5MB</small>
                    </div>
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Petunjuk Import:</h6>
                        <ul class="mb-0 small">
                            <li>Download template terlebih dahulu untuk format yang benar</li>
                            <li>Kolom wajib: <strong>nama_jurnal</strong>, <strong>volume</strong>, <strong>nomor</strong>, <strong>bulan</strong></li>
                            <li>Kolom opsional: kode_slot, tahun, jumlah_slot, status</li>
                            <li><strong>Nama jurnal harus sudah ada di database!</strong></li>
                            <li>Jika slot sudah ada (volume, nomor, tahun sama), data akan diperbarui</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
