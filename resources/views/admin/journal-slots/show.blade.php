@extends('layouts.app')

@section('title', 'Detail Slot - ' . $appSettings['app_name'])
@section('page-title', 'Detail Slot')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar3"></i> Detail Slot</span>
                <div>
                    <a href="{{ route('admin.journal-slots.edit', $journalSlot) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('admin.journal-slots.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Kode Slot</th>
                                <td><code>{{ $journalSlot->kode_slot }}</code></td>
                            </tr>
                            <tr>
                                <th>Nama Jurnal</th>
                                <td>
                                    <a href="{{ route('admin.journal-masters.show', $journalSlot->journalMaster) }}">
                                        {{ $journalSlot->journalMaster->nama_jurnal }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Publisher</th>
                                <td>{{ $journalSlot->journalMaster->publisher }}</td>
                            </tr>
                            <tr>
                                <th>Volume</th>
                                <td>{{ $journalSlot->volume }}</td>
                            </tr>
                            <tr>
                                <th>Nomor</th>
                                <td>{{ $journalSlot->nomor }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Bulan</th>
                                <td>{{ $journalSlot->bulan }}</td>
                            </tr>
                            <tr>
                                <th>Tahun</th>
                                <td>{{ $journalSlot->tahun }}</td>
                            </tr>
                            <tr>
                                <th>Jumlah Slot</th>
                                <td><span class="badge bg-secondary fs-6">{{ $journalSlot->jumlah_slot }}</span></td>
                            </tr>
                            <tr>
                                <th>Slot Terpakai</th>
                                <td><span class="badge bg-warning fs-6">{{ $journalSlot->slot_terpakai }}</span></td>
                            </tr>
                            <tr>
                                <th>Slot Tersedia</th>
                                <td><span class="badge bg-success fs-6">{{ $journalSlot->slot_tersedia }}</span></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($journalSlot->is_full)
                                        <span class="badge bg-danger">Penuh</span>
                                    @elseif($journalSlot->is_active)
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
                    $percentage = $journalSlot->jumlah_slot > 0 ? ($journalSlot->slot_terpakai / $journalSlot->jumlah_slot) * 100 : 0;
                    $progressClass = $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success');
                @endphp
                <div class="mt-3">
                    <label class="form-label">Penggunaan Slot</label>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $percentage }}%">
                            {{ round($percentage, 1) }}% ({{ $journalSlot->slot_terpakai }}/{{ $journalSlot->jumlah_slot }})
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submissions List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text"></i> Daftar Submission</span>
                @if(!$journalSlot->is_full && $journalSlot->is_active)
                <a href="{{ route('admin.submissions.create', ['journal_slot_id' => $journalSlot->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Submit
                </a>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kode Submit</th>
                                <th>ID Artikel</th>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Tanggal Submit</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journalSlot->submissions as $submission)
                            <tr>
                                <td><code>{{ $submission->kode_submit }}</code></td>
                                <td>{{ $submission->id_artikel }}</td>
                                <td>{{ Str::limit($submission->judul_artikel, 40) }}</td>
                                <td>{{ $submission->nama_penulis }}</td>
                                <td>{{ $submission->tanggal_submit?->format('d M Y') }}</td>
                                <td><span class="badge {{ $submission->status_badge_class }}">{{ $submission->status_label }}</span></td>
                                <td>
                                    <a href="{{ route('admin.submissions.show', $submission) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
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
</div>
@endsection
