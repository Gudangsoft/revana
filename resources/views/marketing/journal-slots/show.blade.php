@extends('marketing.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-calendar3"></i> Detail Slot</h4>
        <a href="{{ route('marketing.journal-slots.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Slot Info -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Slot</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="150" class="text-muted">Kode Slot</th>
                            <td><code class="fs-6">{{ $slot->kode_slot }}</code></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Nama Jurnal</th>
                            <td><strong class="text-primary">{{ $slot->journalMaster->nama_jurnal }}</strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Publisher</th>
                            <td>{{ $slot->journalMaster->publisher ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Akreditasi</th>
                            <td>
                                @if($slot->journalMaster->accreditation)
                                    <span class="badge bg-info">{{ $slot->journalMaster->accreditation }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Kategori</th>
                            <td>
                                @if($slot->journalMaster->kategori)
                                    <span class="badge bg-primary">{{ $slot->journalMaster->kategori }}</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="150" class="text-muted">Volume</th>
                            <td>{{ $slot->volume ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Nomor</th>
                            <td>{{ $slot->nomor ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Bulan / Tahun</th>
                            <td>{{ $slot->bulan }} / {{ $slot->tahun }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Jumlah Slot</th>
                            <td><span class="badge bg-secondary fs-6">{{ $slot->jumlah_slot }}</span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Terpakai / Tersedia</th>
                            <td>
                                <span class="badge bg-warning fs-6">{{ $slot->slot_terpakai }}</span>
                                /
                                <span class="badge bg-success fs-6">{{ $slot->slot_tersedia }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Status</th>
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
                    </table>
                </div>
            </div>

            <!-- Progress Bar -->
            @php
                $percentage = $slot->jumlah_slot > 0 ? ($slot->slot_terpakai / $slot->jumlah_slot) * 100 : 0;
                $progressClass = $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success');
            @endphp
            <div class="mt-3">
                <label class="form-label text-muted">Penggunaan Slot</label>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $percentage }}%">
                        {{ round($percentage, 1) }}% ({{ $slot->slot_terpakai }}/{{ $slot->jumlah_slot }})
                    </div>
                </div>
            </div>

            @if(!$slot->is_full && $slot->is_active)
            <div class="mt-3">
                <a href="{{ route('marketing.submissions.create', ['journal_master_id' => $slot->journal_master_id, 'journal_slot_id' => $slot->id]) }}" 
                   class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Submit Artikel ke Slot Ini
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Submissions for this slot (only marketing's own) -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-file-earmark-text"></i> Submission Anda di Slot Ini</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Submit</th>
                            <th>ID Artikel</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Tanggal Submit</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slot->submissions as $submission)
                        <tr>
                            <td><code>{{ $submission->kode_submit }}</code></td>
                            <td>{{ $submission->id_artikel ?? '-' }}</td>
                            <td>{{ Str::limit($submission->judul_artikel, 40) }}</td>
                            <td>{{ $submission->nama_penulis }}</td>
                            <td>{{ $submission->tanggal_submit?->format('d M Y') ?? '-' }}</td>
                            <td><span class="badge {{ $submission->status_badge_class }}">{{ $submission->status_label }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 8px; min-width: 60px;">
                                        <div class="progress-bar {{ $submission->status_badge_class }}" 
                                             style="width: {{ $submission->progress_percentage }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $submission->progress_percentage }}%</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('marketing.submissions.show', $submission) }}" 
                                   class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Anda belum memiliki submission di slot ini
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
