@extends('marketing.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-calendar3"></i> Data Slot Jurnal</h4>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('marketing.journal-slots.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Jurnal</label>
                    <select name="journal_master_id" class="form-select form-select-sm">
                        <option value="">-- Semua Jurnal --</option>
                        @foreach($journals as $j)
                            <option value="{{ $j->id }}" {{ request('journal_master_id') == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jurnal }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Tahun</label>
                    <input type="number" name="tahun" class="form-control form-control-sm" value="{{ request('tahun') }}" placeholder="2026">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="{{ route('marketing.journal-slots.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Kode Slot</th>
                            <th>Jurnal</th>
                            <th>Volume</th>
                            <th>Nomor</th>
                            <th>Bulan/Tahun</th>
                            <th>Slot</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slots as $index => $slot)
                        @php
                            $slotTersedia = $slot->jumlah_slot - $slot->slot_terpakai;
                            $percentage = $slot->jumlah_slot > 0 ? ($slot->slot_terpakai / $slot->jumlah_slot) * 100 : 0;
                        @endphp
                        <tr>
                            <td>{{ $slots->firstItem() + $index }}</td>
                            <td><code>{{ $slot->kode_slot }}</code></td>
                            <td>{{ $slot->journalMaster->nama_jurnal ?? '-' }}</td>
                            <td><span class="badge bg-secondary">Vol. {{ $slot->volume }}</span></td>
                            <td><span class="badge bg-secondary">No. {{ $slot->nomor }}</span></td>
                            <td>{{ $slot->bulan }} {{ $slot->tahun }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width: 100px; height: 20px;">
                                        <div class="progress-bar {{ $percentage >= 80 ? 'bg-danger' : ($percentage >= 50 ? 'bg-warning' : 'bg-success') }}" 
                                             role="progressbar" 
                                             style="width: {{ $percentage }}%"
                                             aria-valuenow="{{ $slot->slot_terpakai }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="{{ $slot->jumlah_slot }}">
                                        </div>
                                    </div>
                                    <small>
                                        <strong>{{ $slot->slot_terpakai }}</strong> / {{ $slot->jumlah_slot }}
                                    </small>
                                </div>
                                @if($slotTersedia > 0)
                                    <small class="text-success">
                                        <i class="bi bi-check-circle-fill"></i> {{ $slotTersedia }} tersedia
                                    </small>
                                @else
                                    <small class="text-danger">
                                        <i class="bi bi-x-circle-fill"></i> Penuh
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($slot->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2 mb-0">Tidak ada data slot</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $slots->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
