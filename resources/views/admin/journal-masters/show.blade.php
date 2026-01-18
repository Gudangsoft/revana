@extends('layouts.app')

@section('title', 'Detail Jurnal - ' . $appSettings['app_name'])
@section('page-title', 'Detail Jurnal')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text"></i> Detail Jurnal</span>
                <div>
                    <a href="{{ route('admin.journal-masters.edit', $journalMaster) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('admin.journal-masters.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Kode Jurnal</th>
                                <td><code>{{ $journalMaster->kode_jurnal }}</code></td>
                            </tr>
                            <tr>
                                <th>Nama Jurnal</th>
                                <td>{{ $journalMaster->nama_jurnal }}</td>
                            </tr>
                            <tr>
                                <th>Publisher</th>
                                <td>{{ $journalMaster->publisher }}</td>
                            </tr>
                            <tr>
                                <th>Link Jurnal</th>
                                <td>
                                    <a href="{{ $journalMaster->link_jurnal }}" target="_blank">
                                        {{ Str::limit($journalMaster->link_jurnal, 50) }}
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Akreditasi</th>
                                <td>
                                    @if($journalMaster->accreditation)
                                        <span class="badge bg-info">{{ $journalMaster->accreditation }}</span>
                                        <span class="badge bg-success">{{ $journalMaster->points }} pts</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($journalMaster->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Total Slot</th>
                                <td>
                                    <span class="badge bg-primary">{{ $journalMaster->total_slots }}</span>
                                    <span class="badge bg-warning">{{ $journalMaster->used_slots }} Terpakai</span>
                                    <span class="badge bg-success">{{ $journalMaster->available_slots }} Tersedia</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Dibuat</th>
                                <td>{{ $journalMaster->created_at->format('d M Y H:i') }} oleh {{ $journalMaster->creator->name }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slot List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar3"></i> Daftar Slot</span>
                <a href="{{ route('admin.journal-slots.create', ['journal_master_id' => $journalMaster->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Slot
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kode Slot</th>
                                <th>Volume</th>
                                <th>Nomor</th>
                                <th>Bulan</th>
                                <th>Tahun</th>
                                <th>Jumlah Slot</th>
                                <th>Terpakai</th>
                                <th>Tersedia</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journalMaster->slots as $slot)
                            <tr>
                                <td><code>{{ $slot->kode_slot }}</code></td>
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    Belum ada slot untuk jurnal ini
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
