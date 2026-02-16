@extends('pic.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-calendar3"></i> Detail Slot</h4>
        <div>
            <a href="{{ route('pic.journal-slots.edit', $slot) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('pic.journal-slots.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Slot Info -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Slot</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="150">Kode Slot</th>
                            <td><code class="fs-6">{{ $slot->kode_slot }}</code></td>
                        </tr>
                        <tr>
                            <th>Nama Jurnal</th>
                            <td><strong>{{ $slot->journalMaster->nama_jurnal }}</strong></td>
                        </tr>
                        <tr>
                            <th>Publisher</th>
                            <td>{{ $slot->journalMaster->publisher ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Akreditasi</th>
                            <td>
                                @if($slot->journalMaster->accreditation)
                                    <span class="badge bg-info">{{ $slot->journalMaster->accreditation }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Volume / Nomor</th>
                            <td>Vol. {{ $slot->volume ?? '-' }} No. {{ $slot->nomor ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="150">Bulan / Tahun</th>
                            <td>{{ $slot->bulan }} / {{ $slot->tahun }}</td>
                        </tr>
                        <tr>
                            <th>Jumlah Slot</th>
                            <td><span class="badge bg-secondary fs-6">{{ $slot->jumlah_slot }}</span></td>
                        </tr>
                        <tr>
                            <th>Slot Terpakai</th>
                            <td><span class="badge bg-warning fs-6">{{ $slot->slot_terpakai }}</span></td>
                        </tr>
                        <tr>
                            <th>Slot Tersedia</th>
                            <td><span class="badge bg-success fs-6">{{ $slot->slot_tersedia }}</span></td>
                        </tr>
                        <tr>
                            <th>Status</th>
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
                <label class="form-label">Penggunaan Slot</label>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $percentage }}%">
                        {{ round($percentage, 1) }}% ({{ $slot->slot_terpakai }}/{{ $slot->jumlah_slot }})
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submissions List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-file-earmark-text"></i> Daftar Submission</h6>
            @if(!$slot->is_full && $slot->is_active)
            <a href="{{ route('pic.submissions.create', ['journal_slot_id' => $slot->id]) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Submit
            </a>
            @endif
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
                            <th>Marketing</th>
                            <th>Tanggal Submit</th>
                            <th>Status</th>
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
                            <td>{{ $submission->marketing->name ?? '-' }}</td>
                            <td>{{ $submission->tanggal_submit?->format('d M Y') ?? '-' }}</td>
                            <td><span class="badge {{ $submission->status_badge_class }}">{{ $submission->status_label }}</span></td>
                            <td class="text-center">
                                <a href="{{ route('pic.submissions.show', $submission) }}" 
                                   class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Belum ada submission untuk slot ini
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
