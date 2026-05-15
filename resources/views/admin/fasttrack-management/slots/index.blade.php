@extends('layouts.app')

@section('title', 'Data Slot FS - ' . $appSettings['app_name'])
@section('page-title', 'Data Slot FS')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar3"></i> Data Slot Fasttrack</span>
                <div class="btn-group">
                    <a href="{{ route('admin.journal-slots.template') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-file-earmark-arrow-down"></i> Template
                    </a>
                    <a href="{{ route('admin.journal-slots.export', request()->query()) }}" class="btn btn-info">
                        <i class="bi bi-download"></i> Export
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

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> <strong>Data Slot Fasttrack:</strong> Menampilkan slot jurnal yang dapat digunakan untuk proses fasttrack.
                </div>

                <!-- Search & Filter Form -->
                <form action="{{ route('admin.fasttrack-management.slots.index') }}" method="GET" class="mb-4">
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
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="sort_by">
                                <option value="">-- Urutkan --</option>
                                <option value="volume_asc" {{ request('sort_by') == 'volume_asc' ? 'selected' : '' }}>Volume (Asc)</option>
                                <option value="volume_desc" {{ request('sort_by') == 'volume_desc' ? 'selected' : '' }}>Volume (Desc)</option>
                                <option value="nomor_asc" {{ request('sort_by') == 'nomor_asc' ? 'selected' : '' }}>Nomor (Asc)</option>
                                <option value="nomor_desc" {{ request('sort_by') == 'nomor_desc' ? 'selected' : '' }}>Nomor (Desc)</option>
                                <option value="latest" {{ request('sort_by') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                                <a href="{{ route('admin.fasttrack-management.slots.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Kode Slot</th>
                                <th>Jurnal</th>
                                <th>Vol/No/Tahun</th>
                                <th>Bulan</th>
                                <th>Slot</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slots as $slot)
                            <tr>
                                <td>
                                    <code>{{ $slot->kode_slot }}</code>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $slot->journalMaster->nama_jurnal }}</strong>
                                        <br><small class="text-muted">{{ $slot->journalMaster->publisher }}</small>
                                    </div>
                                </td>
                                <td>
                                    Vol. {{ $slot->volume }} No. {{ $slot->nomor }} ({{ $slot->tahun }})
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $bulanOptions[$slot->bulan] ?? $slot->bulan }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                            @php
                                                $percentage = $slot->jumlah_slot > 0 ? ($slot->slot_terpakai / $slot->jumlah_slot * 100) : 0;
                                                $bgClass = $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success');
                                            @endphp
                                            <div class="progress-bar {{ $bgClass }}" role="progressbar" style="width: {{ $percentage }}%">
                                                {{ round($percentage) }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $slot->slot_terpakai }}/{{ $slot->jumlah_slot }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($slot->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.journal-slots.show', $slot) }}" class="btn btn-outline-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.journal-slots.edit', $slot) }}" class="btn btn-outline-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger"
                                                onclick="confirmDelete('{{ route('admin.journal-slots.destroy', $slot) }}', '{{ $slot->kode_slot }}', {{ $slot->slot_terpakai ?? 0 }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Tidak ada data slot fasttrack yang ditemukan
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @include('partials.per-page-selector', ['paginator' => $slots, 'default' => 20])
            </div>
        </div>
    </div>
</div>

{{-- Hidden delete form --}}
<form id="deleteSlotForm" method="POST" style="display:none">
    @csrf @method('DELETE')
</form>

@push('scripts')
<script>
function confirmDelete(url, kode, terpakai) {
    if (terpakai > 0) {
        alert('Slot ' + kode + ' tidak dapat dihapus karena sudah memiliki ' + terpakai + ' submission.');
        return;
    }
    if (!confirm('Hapus slot ' + kode + '?\nData tidak dapat dipulihkan.')) return;
    var form = document.getElementById('deleteSlotForm');
    form.action = url;
    form.submit();
}
</script>
@endpush

@endsection