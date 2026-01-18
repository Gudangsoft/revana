@extends('layouts.app')

@section('title', 'Detail Submit - ' . $appSettings['app_name'])
@section('page-title', 'Detail Submit')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text"></i> Detail Submit</span>
                <div>
                    <a href="{{ route('admin.submissions.process', $submission) }}" class="btn btn-info">
                        <i class="bi bi-gear"></i> Lihat Proses
                    </a>
                    <a href="{{ route('admin.submissions.edit', $submission) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('admin.submissions.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Progress Bar -->
                <div class="mb-4">
                    <label class="form-label">Progress</label>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar {{ $submission->status === 'REJECTED' ? 'bg-danger' : 'bg-success' }}" role="progressbar" style="width: {{ $submission->progress_percentage }}%">
                            {{ round($submission->progress_percentage) }}%
                        </div>
                    </div>
                    <div class="text-center mt-2">
                        <span class="badge {{ $submission->status_badge_class }} fs-6">{{ $submission->status_label }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-file-text"></i> Data Submit</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="150">Kode Submit</th>
                                <td><code>{{ $submission->kode_submit }}</code></td>
                            </tr>
                            <tr>
                                <th>ID Artikel</th>
                                <td>{{ $submission->id_artikel }}</td>
                            </tr>
                            <tr>
                                <th>Judul Artikel</th>
                                <td>{{ $submission->judul_artikel }}</td>
                            </tr>
                            <tr>
                                <th>Link Artikel</th>
                                <td>
                                    @if($submission->link_artikel)
                                        <a href="{{ $submission->link_artikel }}" target="_blank">
                                            {{ Str::limit($submission->link_artikel, 40) }} <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Submit</th>
                                <td>{{ $submission->tanggal_submit?->format('d M Y') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-person"></i> Data Penulis</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="150">Nama Penulis</th>
                                <td>{{ $submission->nama_penulis }}</td>
                            </tr>
                            <tr>
                                <th>No HP</th>
                                <td>{{ $submission->no_hp_penulis ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Username Author</th>
                                <td><code>{{ $submission->username_author ?? '-' }}</code></td>
                            </tr>
                            <tr>
                                <th>Password Author</th>
                                <td><code>{{ $submission->password_author ?? '-' }}</code></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-journal-text"></i> Data Jurnal & Slot</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="150">Jurnal</th>
                                <td>
                                    <a href="{{ route('admin.journal-masters.show', $submission->journalSlot->journalMaster) }}">
                                        {{ $submission->journalSlot->journalMaster->nama_jurnal }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Publisher</th>
                                <td>{{ $submission->journalSlot->journalMaster->publisher }}</td>
                            </tr>
                            <tr>
                                <th>Slot</th>
                                <td>
                                    <a href="{{ route('admin.journal-slots.show', $submission->journalSlot) }}">
                                        Vol. {{ $submission->journalSlot->volume }} No. {{ $submission->journalSlot->nomor }} - {{ $submission->journalSlot->bulan }} {{ $submission->journalSlot->tahun }}
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-people"></i> PIC & Petugas</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="150">PIC Marketing</th>
                                <td>{{ $submission->pic_marketing ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Petugas Submit</th>
                                <td>{{ $submission->petugasSubmit?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Dibuat</th>
                                <td>{{ $submission->created_at->format('d M Y H:i') }} oleh {{ $submission->creator?->name }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($submission->link_publish)
                <div class="alert alert-success mt-3">
                    <i class="bi bi-check-circle"></i> <strong>Artikel sudah dipublikasi!</strong><br>
                    <a href="{{ $submission->link_publish }}" target="_blank">{{ $submission->link_publish }}</a>
                </div>
                @endif

                @if($submission->notes)
                <div class="mt-3">
                    <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-sticky"></i> Catatan</h6>
                    <p>{{ $submission->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
