@extends('layouts.app')

@section('title', 'Proses Submit - ' . $appSettings['app_name'])
@section('page-title', 'Proses Submit')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-x-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Header Info -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-gear"></i> Proses Submit: <code>{{ $submission->kode_submit }}</code></span>
                <div>
                    <a href="{{ route('admin.submissions.history', $submission) }}" class="btn btn-outline-info">
                        <i class="bi bi-clock-history"></i> Lihat Histori
                    </a>
                    <a href="{{ route('admin.submissions.show', $submission) }}" class="btn btn-info">
                        <i class="bi bi-eye"></i> Detail
                    </a>
                    <a href="{{ route('admin.submissions.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>ID Artikel:</strong> {{ $submission->id_artikel }}<br>
                        <strong>Judul:</strong> {{ Str::limit($submission->judul_artikel, 50) }}<br>
                        <strong>Penulis:</strong> {{ $submission->nama_penulis }}
                    </div>
                    <div class="col-md-4">
                        <strong>No HP:</strong> {{ $submission->no_hp_penulis ?? '-' }}<br>
                        <strong>Username Author:</strong> <code>{{ $submission->username_author ?? '-' }}</code><br>
                        <strong>Password Author:</strong> <code>{{ $submission->password_author ?? '-' }}</code>
                    </div>
                    <div class="col-md-4">
                        <strong>PIC Marketing:</strong> {{ $submission->pic_marketing ?? '-' }}<br>
                        <strong>Petugas Submit:</strong> {{ $submission->petugasSubmit?->name ?? '-' }}<br>
                        <strong>Tanggal Submit:</strong> {{ $submission->tanggal_submit?->format('d M Y') }}
                    </div>
                </div>
                
                <!-- Progress Bar -->
                <div class="mt-3">
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar {{ $submission->status === 'REJECTED' ? 'bg-danger' : 'bg-success' }}" role="progressbar" style="width: {{ $submission->progress_percentage }}%">
                            {{ round($submission->progress_percentage) }}% - {{ $submission->status_label }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workflow Steps -->
        <div class="row">
            @php
                $stepsConfig = [
                    'editor1' => [
                        'title' => 'Editor 1',
                        'icon' => '1',
                        'desc' => 'Input user & password editor dan tanya jawab ke penulis',
                        'valid_field' => 'editor1_valid',
                        'validated_at_field' => 'editor1_validated_at',
                        'petugas_rel' => 'petugasEditor1',
                        'petugas_id_field' => 'petugas_editor1_id',
                        'has_credentials' => true,
                        'credential_fields' => ['username_editor', 'password_editor'],
                        'prev_step_valid' => true,
                    ],
                    'author1' => [
                        'title' => 'Author 1',
                        'icon' => '2',
                        'desc' => 'Menjawab pertanyaan dari Editor 1',
                        'valid_field' => 'author1_valid',
                        'validated_at_field' => 'author1_validated_at',
                        'petugas_rel' => 'petugasAuthor1',
                        'petugas_id_field' => 'petugas_author1_id',
                        'has_credentials' => false,
                        'prev_step_valid' => $submission->editor1_valid,
                    ],
                    'editor2' => [
                        'title' => 'Editor 2',
                        'icon' => '3',
                        'desc' => 'Input user Reviewer 1 & 2 (penugasan reviewer)',
                        'valid_field' => 'editor2_valid',
                        'validated_at_field' => 'editor2_validated_at',
                        'petugas_rel' => 'petugasEditor2',
                        'petugas_id_field' => 'petugas_editor2_id',
                        'has_credentials' => false,
                        'prev_step_valid' => $submission->author1_valid,
                    ],
                    'reviewer1' => [
                        'title' => 'Reviewer 1',
                        'icon' => '4',
                        'desc' => 'Menyelesaikan review (catatan dan form review)',
                        'valid_field' => 'reviewer1_valid',
                        'validated_at_field' => 'reviewer1_validated_at',
                        'petugas_rel' => 'petugasReviewer1',
                        'petugas_id_field' => 'petugas_reviewer1_id',
                        'has_credentials' => true,
                        'credential_fields' => ['username_reviewer1', 'password_reviewer1'],
                        'has_notes' => true,
                        'notes_field' => 'catatan_reviewer1',
                        'prev_step_valid' => $submission->editor2_valid,
                    ],
                    'reviewer2' => [
                        'title' => 'Reviewer 2',
                        'icon' => '5',
                        'desc' => 'Menyelesaikan review (catatan dan form review)',
                        'valid_field' => 'reviewer2_valid',
                        'validated_at_field' => 'reviewer2_validated_at',
                        'petugas_rel' => 'petugasReviewer2',
                        'petugas_id_field' => 'petugas_reviewer2_id',
                        'has_credentials' => true,
                        'credential_fields' => ['username_reviewer2', 'password_reviewer2'],
                        'has_notes' => true,
                        'notes_field' => 'catatan_reviewer2',
                        'prev_step_valid' => $submission->reviewer1_valid,
                    ],
                    'editor3' => [
                        'title' => 'Editor 3',
                        'icon' => '6',
                        'desc' => 'Mengirimkan ke penulis revisi',
                        'valid_field' => 'editor3_valid',
                        'validated_at_field' => 'editor3_validated_at',
                        'petugas_rel' => 'petugasEditor3',
                        'petugas_id_field' => 'petugas_editor3_id',
                        'has_credentials' => false,
                        'prev_step_valid' => $submission->reviewer2_valid,
                    ],
                    'author2' => [
                        'title' => 'Author 2',
                        'icon' => '7',
                        'desc' => 'Mengirimkan hasil revisi ke OJS',
                        'valid_field' => 'author2_valid',
                        'validated_at_field' => 'author2_validated_at',
                        'petugas_rel' => 'petugasAuthor2',
                        'petugas_id_field' => 'petugas_author2_id',
                        'has_credentials' => false,
                        'prev_step_valid' => $submission->editor3_valid,
                    ],
                    'production' => [
                        'title' => 'Production',
                        'icon' => '8',
                        'desc' => 'Editing dan publish',
                        'valid_field' => 'production_valid',
                        'validated_at_field' => 'production_validated_at',
                        'petugas_rel' => 'petugasProduction',
                        'petugas_id_field' => 'petugas_production_id',
                        'has_credentials' => false,
                        'has_link_publish' => true,
                        'prev_step_valid' => $submission->author2_valid,
                    ],
                ];
            @endphp

            @foreach($stepsConfig as $stepKey => $stepCfg)
                @php
                    $isValid = $submission->{$stepCfg['valid_field']};
                    $validatedAt = $submission->{$stepCfg['validated_at_field']};
                    $petugasId = $submission->{$stepCfg['petugas_id_field']};
                    $petugas = $submission->{$stepCfg['petugas_rel']};
                    
                    $stepHistories = $historiesByStep[$stepKey] ?? collect();
                    $revisionCount = $stepHistories->where('action', 'revision_request')->count();
                    $lastRevision = $stepHistories->where('action', 'revision_request')->sortByDesc('created_at')->first();
                    $lastRevisionSubmit = $stepHistories->where('action', 'revision_submit')->sortByDesc('created_at')->first();
                    $hasActiveRevision = $lastRevision && (!$lastRevisionSubmit || $lastRevision->created_at > $lastRevisionSubmit->created_at);
                    
                    // Check if step is submitted (waiting for admin validation)
                    $stepUpperKey = strtoupper($stepKey);
                    $isSubmitted = str_contains($submission->status, $stepUpperKey . '_SUBMITTED');
                    $lastSubmittedHistory = $stepHistories->where('action', 'submitted')->sortByDesc('created_at')->first();
                    
                    // Credential check
                    $hasRequiredCredentials = true;
                    if (isset($stepCfg['has_credentials']) && $stepCfg['has_credentials']) {
                        $hasRequiredCredentials = !empty($submission->{$stepCfg['credential_fields'][0]});
                    }
                    
                    $canValidate = $petugasId && $hasRequiredCredentials && $stepCfg['prev_step_valid'];
                @endphp
                
                <div class="col-md-6 mb-4">
                    <div class="card {{ $isValid ? 'border-success' : ($isSubmitted ? 'border-info' : ($hasActiveRevision ? 'border-warning' : '')) }}">
                        <div class="card-header d-flex justify-content-between align-items-center {{ $isValid ? 'bg-success text-white' : ($isSubmitted ? 'bg-info text-white' : ($hasActiveRevision ? 'bg-warning' : '')) }}">
                            <span>
                                <i class="bi {{ $isValid ? 'bi-check-circle-fill' : 'bi-'.$stepCfg['icon'].'-circle' }}"></i>
                                Petugas {{ $stepCfg['title'] }}
                                @if($revisionCount > 0)
                                    <span class="badge bg-warning text-dark ms-1">{{ $revisionCount }} Revisi</span>
                                @endif
                            </span>
                            @if($isValid)
                                <span class="badge bg-light text-success">Valid</span>
                            @elseif($isSubmitted)
                                <span class="badge bg-light text-info">
                                    <i class="bi bi-hourglass-split"></i> Menunggu Validasi Admin
                                </span>
                            @elseif($hasActiveRevision)
                                <span class="badge bg-light text-warning">Menunggu Revisi</span>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($isSubmitted && $lastSubmittedHistory)
                            <div class="alert alert-info py-2 mb-2">
                                <i class="bi bi-info-circle"></i> 
                                <strong>PIC sudah selesai mengerjakan!</strong><br>
                                <small>Diserahkan: {{ $lastSubmittedHistory->created_at->format('d M Y H:i') }}</small>
                                @if($lastSubmittedHistory->notes)
                                <br><small class="fst-italic">Catatan: {{ $lastSubmittedHistory->notes }}</small>
                                @endif
                            </div>
                            @endif
                            
                            <small class="text-muted d-block mb-2">Bertugas: {{ $stepCfg['desc'] }}</small>
                            
                            <!-- Form Penugasan -->
                            <form action="{{ route('admin.submissions.update-process', $submission) }}" method="POST">
                                @csrf
                                <input type="hidden" name="step" value="{{ $stepKey }}">
                                
                                <div class="mb-2">
                                    <label class="form-label">Petugas {{ $stepCfg['title'] }}</label>
                                    <select class="form-select form-select-sm" name="petugas_{{ $stepKey }}_id" {{ $isValid ? 'disabled' : '' }}>
                                        <option value="">-- Pilih Petugas --</option>
                                        @if(in_array($stepKey, ['reviewer1', 'reviewer2']))
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $petugasId == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                            @endforeach
                                        @else
                                            @foreach($pics as $pic)
                                                <option value="{{ $pic->id }}" {{ $petugasId == $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                
                                @if(isset($stepCfg['has_credentials']) && $stepCfg['has_credentials'])
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control form-control-sm" name="{{ $stepCfg['credential_fields'][0] }}" value="{{ $submission->{$stepCfg['credential_fields'][0]} }}" {{ $isValid ? 'disabled' : '' }}>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">Password</label>
                                            <input type="text" class="form-control form-control-sm" name="{{ $stepCfg['credential_fields'][1] }}" value="{{ $submission->{$stepCfg['credential_fields'][1]} }}" {{ $isValid ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                @endif
                                
                                @if(isset($stepCfg['has_link_publish']) && $stepCfg['has_link_publish'])
                                    <div class="mb-2">
                                        <label class="form-label">Link Publish</label>
                                        <input type="url" class="form-control form-control-sm" name="link_publish" value="{{ $submission->link_publish }}" placeholder="https://" {{ $isValid ? 'disabled' : '' }}>
                                    </div>
                                @endif
                                
                                @if(!$isValid)
                                <button type="submit" class="btn btn-sm btn-primary mt-2">
                                    <i class="bi bi-save"></i> Simpan
                                </button>
                                @endif
                            </form>
                            
                            @if(isset($stepCfg['has_notes']) && $stepCfg['has_notes'])
                                <!-- Catatan Reviewer -->
                                <form action="{{ route('admin.submissions.update-reviewer-notes', $submission) }}" method="POST" class="mt-2">
                                    @csrf
                                    <label class="form-label">Catatan Review</label>
                                    <textarea class="form-control form-control-sm" name="{{ $stepCfg['notes_field'] }}" rows="2">{{ $submission->{$stepCfg['notes_field']} }}</textarea>
                                    <button type="submit" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="bi bi-save"></i> Simpan Catatan
                                    </button>
                                </form>
                            @endif
                            
                            <!-- Tombol Aksi Revisi & Validasi -->
                            @if(!$isValid && $petugasId)
                                <div class="mt-3 d-flex gap-2 flex-wrap">
                                    <!-- Tombol Minta Revisi -->
                                    <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#revisionModal{{ $stepKey }}">
                                        <i class="bi bi-arrow-return-left"></i> Minta Revisi
                                    </button>
                                    
                                    <!-- Tombol Kirim Revisi (jika ada revisi aktif) -->
                                    @if($hasActiveRevision)
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#submitRevisionModal{{ $stepKey }}">
                                            <i class="bi bi-arrow-return-right"></i> Kirim Revisi
                                        </button>
                                    @endif
                                    
                                    <!-- Tombol Validasi -->
                                    @if($canValidate)
                                        <form action="{{ route('admin.submissions.validate-step', $submission) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="step" value="{{ $stepKey }}">
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Validasi langkah ini?')">
                                                <i class="bi bi-check-lg"></i> Validasi
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                            
                            @if($validatedAt)
                            <small class="text-success mt-2 d-block">
                                <i class="bi bi-clock"></i> Divalidasi: {{ $validatedAt->format('d/m/Y H:i') }}
                            </small>
                            @endif
                            
                            <!-- Histori Step (Collapsed) -->
                            @if($stepHistories->count() > 0)
                            <div class="mt-3">
                                <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#history{{ $stepKey }}">
                                    <i class="bi bi-clock-history"></i> Lihat Histori ({{ $stepHistories->count() }})
                                </button>
                                <div class="collapse" id="history{{ $stepKey }}">
                                    <div class="mt-2">
                                        <div class="list-group list-group-flush small">
                                            @foreach($stepHistories->sortByDesc('created_at')->take(5) as $h)
                                            <div class="list-group-item px-0 py-1 border-0">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>
                                                        <span class="badge {{ $h->action_badge_class }} badge-sm">{{ $h->action_label }}@if($h->revision_number > 0) #{{ $h->revision_number }}@endif</span>
                                                        <small class="text-muted">{{ $h->user->name ?? 'System' }}</small>
                                                    </span>
                                                    <small class="text-muted">{{ $h->created_at->format('d/m H:i') }}</small>
                                                </div>
                                                @if($h->notes)
                                                <small class="text-muted fst-italic">{{ Str::limit($h->notes, 50) }}</small>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Modal Minta Revisi -->
                <div class="modal fade" id="revisionModal{{ $stepKey }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('admin.submissions.request-revision', $submission) }}" method="POST">
                                @csrf
                                <input type="hidden" name="step" value="{{ $stepKey }}">
                                <div class="modal-header">
                                    <h5 class="modal-title">Minta Revisi - {{ $stepCfg['title'] }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Catatan Revisi <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="notes" rows="4" required placeholder="Jelaskan apa yang perlu direvisi..."></textarea>
                                    </div>
                                    @if($revisionCount > 0)
                                    <div class="alert alert-info mb-0">
                                        <small><i class="bi bi-info-circle"></i> Ini akan menjadi revisi ke-{{ $revisionCount + 1 }}</small>
                                    </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-arrow-return-left"></i> Kirim Permintaan Revisi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Kirim Revisi -->
                <div class="modal fade" id="submitRevisionModal{{ $stepKey }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('admin.submissions.submit-revision', $submission) }}" method="POST">
                                @csrf
                                <input type="hidden" name="step" value="{{ $stepKey }}">
                                <div class="modal-header">
                                    <h5 class="modal-title">Kirim Revisi - {{ $stepCfg['title'] }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    @if($lastRevision)
                                    <div class="alert alert-warning">
                                        <strong>Permintaan Revisi:</strong><br>
                                        <small class="text-muted">{{ $lastRevision->created_at->format('d/m/Y H:i') }} oleh {{ $lastRevision->user->name ?? 'System' }}</small><br>
                                        {{ $lastRevision->notes }}
                                    </div>
                                    @endif
                                    <div class="mb-3">
                                        <label class="form-label">Catatan Hasil Revisi</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Jelaskan revisi yang telah dilakukan (opsional)..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-arrow-return-right"></i> Kirim Revisi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
