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
                    <a href="{{ route('admin.submissions.loa', $submission) }}" class="btn btn-primary" target="_blank">
                        <i class="bi bi-file-earmark-check"></i> LOA
                    </a>
                    <a href="{{ route('admin.submissions.kwitansi', $submission) }}" class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-receipt"></i> Kwitansi
                    </a>
                    <a href="{{ route('admin.submissions.process', $submission) }}" class="btn btn-info">
                        <i class="bi bi-gear"></i> Lihat Proses
                    </a>
                    <a href="{{ route('admin.submissions.edit', $submission) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    @if(Route::has('admin.submissions.resend-wa'))
                    <form action="{{ route('admin.submissions.resend-wa', $submission) }}" method="POST" class="d-inline" onsubmit="return confirm('Kirim ulang notifikasi WhatsApp ke {{ $submission->no_hp_penulis ?? 'penulis' }}?')">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-whatsapp"></i> Kirim Ulang WA
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('admin.submissions.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Progress Stepper -->
                @php
                    $stages = [
                        ['key' => 'submit',    'label' => 'Disubmit',  'icon' => 'bi-cloud-upload-fill',     'statuses' => ['SUBMITTED']],
                        ['key' => 'editor',    'label' => 'Editorial', 'icon' => 'bi-pencil-square',          'statuses' => ['EDITOR1_PROCESS','AUTHOR1_PROCESS','EDITOR2_PROCESS']],
                        ['key' => 'review',    'label' => 'Review',    'icon' => 'bi-person-check-fill',      'statuses' => ['REVIEWER1_PROCESS','REVIEWER2_PROCESS']],
                        ['key' => 'produksi',  'label' => 'Produksi',  'icon' => 'bi-gear-fill',              'statuses' => ['EDITOR3_PROCESS','AUTHOR2_PROCESS','PRODUCTION_PROCESS','VALIDATOR_PROCESS']],
                        ['key' => 'selesai',   'label' => 'Selesai',   'icon' => 'bi-check-circle-fill',      'statuses' => ['PUBLISHED','REJECTED']],
                    ];
                    $statusOrder = ['SUBMITTED'=>0,'EDITOR1_PROCESS'=>1,'AUTHOR1_PROCESS'=>2,'EDITOR2_PROCESS'=>3,
                                    'REVIEWER1_PROCESS'=>4,'REVIEWER2_PROCESS'=>5,'EDITOR3_PROCESS'=>6,
                                    'AUTHOR2_PROCESS'=>7,'PRODUCTION_PROCESS'=>8,'VALIDATOR_PROCESS'=>9,
                                    'PUBLISHED'=>10,'REJECTED'=>10];
                    $currentOrder = $statusOrder[$submission->status] ?? 0;
                    $isRejected   = $submission->status === 'REJECTED';
                    $isPublished  = $submission->status === 'PUBLISHED';

                    $stageOrder = ['submit'=>0,'editor'=>1,'review'=>2,'produksi'=>3,'selesai'=>4];
                    $activeStage = 0;
                    foreach ($stages as $si => $stage) {
                        foreach ($stage['statuses'] as $st) {
                            if ($st === $submission->status) { $activeStage = $si; break 2; }
                        }
                        $stageMax = max(array_map(fn($s) => $statusOrder[$s] ?? 0, $stage['statuses']));
                        if ($currentOrder > $stageMax) $activeStage = $si + 1;
                    }
                    $activeStage = min($activeStage, count($stages) - 1);
                @endphp

                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between position-relative submission-stepper">
                        {{-- connector line --}}
                        <div class="stepper-line"></div>

                        @foreach($stages as $i => $stage)
                        @php
                            $stageMax = max(array_map(fn($s) => $statusOrder[$s] ?? 0, $stage['statuses']));
                            $isDone    = $currentOrder > $stageMax;
                            $isActive  = $i === $activeStage;
                            $isPending = !$isDone && !$isActive;

                            if ($isActive && $isRejected) {
                                $circleClass = 'stepper-circle-danger';
                                $labelClass  = 'text-danger fw-bold';
                            } elseif ($isDone || ($isActive && $isPublished)) {
                                $circleClass = 'stepper-circle-done';
                                $labelClass  = 'text-success fw-semibold';
                            } elseif ($isActive) {
                                $circleClass = 'stepper-circle-active';
                                $labelClass  = 'text-primary fw-bold';
                            } else {
                                $circleClass = 'stepper-circle-pending';
                                $labelClass  = 'text-muted';
                            }
                        @endphp
                        <div class="stepper-step text-center">
                            <div class="stepper-circle {{ $circleClass }} mx-auto">
                                @if($isDone || ($isActive && $isPublished))
                                    <i class="bi bi-check-lg"></i>
                                @elseif($isActive && $isRejected)
                                    <i class="bi bi-x-lg"></i>
                                @elseif($isActive)
                                    <i class="{{ $stage['icon'] }}"></i>
                                @else
                                    <i class="{{ $stage['icon'] }}"></i>
                                @endif
                            </div>
                            <div class="stepper-label {{ $labelClass }} mt-1">{{ $stage['label'] }}</div>
                            @if($isActive)
                            <div class="stepper-sublabel text-muted">
                                {{ $submission->status_label }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                <style>
                .submission-stepper { padding: 0.5rem 0 1.5rem; }
                .stepper-line {
                    position: absolute; top: 20px; left: 10%; right: 10%; height: 3px;
                    background: #e2e8f0; z-index: 0;
                }
                .stepper-step { flex: 1; position: relative; z-index: 1; }
                .stepper-circle {
                    width: 42px; height: 42px; border-radius: 50%;
                    display: flex; align-items: center; justify-content: center;
                    font-size: 1.1rem; border: 3px solid;
                    transition: all .2s;
                }
                .stepper-circle-done    { background:#16a34a; border-color:#16a34a; color:#fff; }
                .stepper-circle-active  { background:#3b82f6; border-color:#3b82f6; color:#fff; box-shadow:0 0 0 4px rgba(59,130,246,.2); }
                .stepper-circle-danger  { background:#ef4444; border-color:#ef4444; color:#fff; box-shadow:0 0 0 4px rgba(239,68,68,.2); }
                .stepper-circle-pending { background:#fff; border-color:#cbd5e1; color:#94a3b8; }
                .stepper-label  { font-size:.78rem; white-space:nowrap; }
                .stepper-sublabel { font-size:.67rem; white-space:nowrap; }
                </style>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-file-text"></i> Data Submit</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="150">Kode Submit</th>
                                <td><code>{{ $submission->kode_submit }}</code></td>
                            </tr>
                            <tr>
                                <th>Kode LOA</th>
                                <td><code class="bg-success text-white px-2 py-1 rounded">{{ $submission->kode_loa }}</code></td>
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
                                <th>Link Submit</th>
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
                                <th>File Artikel</th>
                                <td>
                                    @if($submission->file_artikel)
                                        <a href="{{ asset('storage/' . $submission->file_artikel) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-file-earmark-word"></i> {{ $submission->file_artikel_original_name ?? 'Download File' }}
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
                                    @if($submission->journalSlot?->journalMaster)
                                    <a href="{{ route('admin.journal-masters.show', $submission->journalSlot->journalMaster) }}">
                                        {{ $submission->journalSlot->journalMaster->nama_jurnal }}
                                    </a>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Publisher</th>
                                <td>{{ $submission->journalSlot?->journalMaster?->publisher ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Slot</th>
                                <td>
                                    @if($submission->journalSlot)
                                    <a href="{{ route('admin.journal-slots.show', $submission->journalSlot) }}">
                                        Vol. {{ $submission->journalSlot->volume }} No. {{ $submission->journalSlot->nomor }} - {{ $submission->journalSlot->bulan }} {{ $submission->journalSlot->tahun }}
                                    </a>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
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

                @if($submission->catatan_marketing)
                <div class="mt-3">
                    <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-chat-left-text text-warning"></i> Catatan dari Marketing</h6>
                    <div class="alert alert-warning mb-0">
                        @if($submission->catatan_marketing_at)
                        <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom border-warning-subtle">
                            <i class="bi bi-clock-history" style="font-size:.85rem;"></i>
                            <small class="fw-semibold">Ditulis: {{ $submission->catatan_marketing_at->format('d/m/Y H:i') }}</small>
                        </div>
                        @endif
                        {{ $submission->catatan_marketing }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Activity Log --}}
@if($activityLogs->isNotEmpty())
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <span class="fw-semibold"><i class="bi bi-clock-history text-secondary"></i> Riwayat Perubahan</span>
                <small class="text-muted ms-2">({{ $activityLogs->count() }} entri)</small>
            </div>
            <div class="card-body p-0">
                <div class="timeline p-3">
                    @foreach($activityLogs as $log)
                    <div class="d-flex gap-3 mb-3">
                        <div class="flex-shrink-0 pt-1">
                            <span class="badge {{ $log->event_badge_class }} rounded-pill px-2 py-1" style="font-size:.7rem;">
                                {{ $log->event_label }}
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="fw-semibold text-dark" style="font-size:.85rem;">
                                    <i class="bi bi-person-circle text-muted me-1"></i>{{ $log->causer_name }}
                                    <span class="badge bg-light text-secondary border" style="font-size:.65rem;">{{ $log->causer_guard }}</span>
                                </span>
                                <small class="text-muted">{{ $log->created_at->format('d M Y H:i') }}</small>
                            </div>
                            @if($log->old_values || $log->new_values)
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0" style="font-size:.8rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:140px;">Field</th>
                                            <th>Sebelum</th>
                                            <th>Sesudah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(array_keys($log->new_values ?? $log->old_values ?? []) as $field)
                                        <tr>
                                            <td class="text-muted">{{ $field }}</td>
                                            <td class="text-danger">{{ $log->old_values[$field] ?? '-' }}</td>
                                            <td class="text-success">{{ $log->new_values[$field] ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                    @if(!$loop->last)<hr class="my-2">@endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
