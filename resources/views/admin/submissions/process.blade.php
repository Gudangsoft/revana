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

        <!-- Header Info -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-gear"></i> Proses Submit: <code>{{ $submission->kode_submit }}</code></span>
                <div>
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
            <!-- Step 1: Editor 1 -->
            <div class="col-md-6 mb-4">
                <div class="card {{ $submission->editor1_valid ? 'border-success' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center {{ $submission->editor1_valid ? 'bg-success text-white' : '' }}">
                        <span>
                            <i class="bi {{ $submission->editor1_valid ? 'bi-check-circle-fill' : 'bi-1-circle' }}"></i>
                            Petugas Editor 1
                        </span>
                        @if($submission->editor1_valid)
                            <span class="badge bg-light text-success">Valid</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-2">Bertugas: Input user & password editor dan tanya jawab ke penulis</small>
                        
                        <form action="{{ route('admin.submissions.update-process', $submission) }}" method="POST">
                            @csrf
                            <input type="hidden" name="step" value="editor1">
                            
                            <div class="mb-2">
                                <label class="form-label">Petugas Editor 1</label>
                                <select class="form-select form-select-sm" name="petugas_editor1_id" {{ $submission->editor1_valid ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Petugas --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $submission->petugas_editor1_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label">Username Editor</label>
                                    <input type="text" class="form-control form-control-sm" name="username_editor" value="{{ $submission->username_editor }}" {{ $submission->editor1_valid ? 'disabled' : '' }}>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Password Editor</label>
                                    <input type="text" class="form-control form-control-sm" name="password_editor" value="{{ $submission->password_editor }}" {{ $submission->editor1_valid ? 'disabled' : '' }}>
                                </div>
                            </div>
                            @if(!$submission->editor1_valid)
                            <div class="mt-2 d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-save"></i> Simpan
                                </button>
                            </div>
                            @endif
                        </form>
                        
                        @if(!$submission->editor1_valid && $submission->petugas_editor1_id && $submission->username_editor && $submission->password_editor)
                        <form action="{{ route('admin.submissions.validate-step', $submission) }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="step" value="editor1">
                            <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Validasi langkah ini?')">
                                <i class="bi bi-check-lg"></i> Validasi
                            </button>
                        </form>
                        @endif
                        
                        @if($submission->editor1_validated_at)
                        <small class="text-success mt-2 d-block">
                            <i class="bi bi-clock"></i> Divalidasi: {{ $submission->editor1_validated_at->format('d/m/Y H:i') }}
                        </small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 2: Author 1 -->
            <div class="col-md-6 mb-4">
                <div class="card {{ $submission->author1_valid ? 'border-success' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center {{ $submission->author1_valid ? 'bg-success text-white' : '' }}">
                        <span>
                            <i class="bi {{ $submission->author1_valid ? 'bi-check-circle-fill' : 'bi-2-circle' }}"></i>
                            Petugas Author 1
                        </span>
                        @if($submission->author1_valid)
                            <span class="badge bg-light text-success">Valid</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-2">Bertugas: Menjawab pertanyaan dari Editor 1</small>
                        
                        <form action="{{ route('admin.submissions.update-process', $submission) }}" method="POST">
                            @csrf
                            <input type="hidden" name="step" value="author1">
                            
                            <div class="mb-2">
                                <label class="form-label">Petugas Author 1</label>
                                <select class="form-select form-select-sm" name="petugas_author1_id" {{ $submission->author1_valid ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Petugas --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $submission->petugas_author1_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(!$submission->author1_valid)
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                            @endif
                        </form>
                        
                        @if(!$submission->author1_valid && $submission->petugas_author1_id && $submission->editor1_valid)
                        <form action="{{ route('admin.submissions.validate-step', $submission) }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="step" value="author1">
                            <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Validasi langkah ini?')">
                                <i class="bi bi-check-lg"></i> Validasi
                            </button>
                        </form>
                        @endif
                        
                        @if($submission->author1_validated_at)
                        <small class="text-success mt-2 d-block">
                            <i class="bi bi-clock"></i> Divalidasi: {{ $submission->author1_validated_at->format('d/m/Y H:i') }}
                        </small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 3: Editor 2 -->
            <div class="col-md-6 mb-4">
                <div class="card {{ $submission->editor2_valid ? 'border-success' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center {{ $submission->editor2_valid ? 'bg-success text-white' : '' }}">
                        <span>
                            <i class="bi {{ $submission->editor2_valid ? 'bi-check-circle-fill' : 'bi-3-circle' }}"></i>
                            Petugas Editor 2
                        </span>
                        @if($submission->editor2_valid)
                            <span class="badge bg-light text-success">Valid</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-2">Bertugas: Input user Reviewer 1 & 2 (penugasan reviewer)</small>
                        
                        <form action="{{ route('admin.submissions.update-process', $submission) }}" method="POST">
                            @csrf
                            <input type="hidden" name="step" value="editor2">
                            
                            <div class="mb-2">
                                <label class="form-label">Petugas Editor 2</label>
                                <select class="form-select form-select-sm" name="petugas_editor2_id" {{ $submission->editor2_valid ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Petugas --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $submission->petugas_editor2_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(!$submission->editor2_valid)
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                            @endif
                        </form>
                        
                        @if(!$submission->editor2_valid && $submission->petugas_editor2_id && $submission->author1_valid)
                        <form action="{{ route('admin.submissions.validate-step', $submission) }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="step" value="editor2">
                            <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Validasi langkah ini?')">
                                <i class="bi bi-check-lg"></i> Validasi
                            </button>
                        </form>
                        @endif
                        
                        @if($submission->editor2_validated_at)
                        <small class="text-success mt-2 d-block">
                            <i class="bi bi-clock"></i> Divalidasi: {{ $submission->editor2_validated_at->format('d/m/Y H:i') }}
                        </small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 4: Reviewer 1 -->
            <div class="col-md-6 mb-4">
                <div class="card {{ $submission->reviewer1_valid ? 'border-success' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center {{ $submission->reviewer1_valid ? 'bg-success text-white' : '' }}">
                        <span>
                            <i class="bi {{ $submission->reviewer1_valid ? 'bi-check-circle-fill' : 'bi-4-circle' }}"></i>
                            Petugas Reviewer 1
                        </span>
                        @if($submission->reviewer1_valid)
                            <span class="badge bg-light text-success">Valid</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-2">Bertugas: Menyelesaikan review (catatan dan form review)</small>
                        
                        <form action="{{ route('admin.submissions.update-process', $submission) }}" method="POST">
                            @csrf
                            <input type="hidden" name="step" value="reviewer1">
                            
                            <div class="mb-2">
                                <label class="form-label">Petugas Reviewer 1</label>
                                <select class="form-select form-select-sm" name="petugas_reviewer1_id" {{ $submission->reviewer1_valid ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Petugas --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $submission->petugas_reviewer1_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control form-control-sm" name="username_reviewer1" value="{{ $submission->username_reviewer1 }}" {{ $submission->reviewer1_valid ? 'disabled' : '' }}>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Password</label>
                                    <input type="text" class="form-control form-control-sm" name="password_reviewer1" value="{{ $submission->password_reviewer1 }}" {{ $submission->reviewer1_valid ? 'disabled' : '' }}>
                                </div>
                            </div>
                            @if(!$submission->reviewer1_valid)
                            <button type="submit" class="btn btn-sm btn-primary mt-2">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                            @endif
                        </form>
                        
                        <!-- Catatan Reviewer 1 -->
                        <form action="{{ route('admin.submissions.update-reviewer-notes', $submission) }}" method="POST" class="mt-2">
                            @csrf
                            <label class="form-label">Catatan Review</label>
                            <textarea class="form-control form-control-sm" name="catatan_reviewer1" rows="2">{{ $submission->catatan_reviewer1 }}</textarea>
                            <button type="submit" class="btn btn-sm btn-outline-primary mt-1">
                                <i class="bi bi-save"></i> Simpan Catatan
                            </button>
                        </form>
                        
                        @if(!$submission->reviewer1_valid && $submission->petugas_reviewer1_id && $submission->username_reviewer1 && $submission->editor2_valid)
                        <form action="{{ route('admin.submissions.validate-step', $submission) }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="step" value="reviewer1">
                            <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Validasi langkah ini?')">
                                <i class="bi bi-check-lg"></i> Validasi
                            </button>
                        </form>
                        @endif
                        
                        @if($submission->reviewer1_validated_at)
                        <small class="text-success mt-2 d-block">
                            <i class="bi bi-clock"></i> Divalidasi: {{ $submission->reviewer1_validated_at->format('d/m/Y H:i') }}
                        </small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 5: Reviewer 2 -->
            <div class="col-md-6 mb-4">
                <div class="card {{ $submission->reviewer2_valid ? 'border-success' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center {{ $submission->reviewer2_valid ? 'bg-success text-white' : '' }}">
                        <span>
                            <i class="bi {{ $submission->reviewer2_valid ? 'bi-check-circle-fill' : 'bi-5-circle' }}"></i>
                            Petugas Reviewer 2
                        </span>
                        @if($submission->reviewer2_valid)
                            <span class="badge bg-light text-success">Valid</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-2">Bertugas: Menyelesaikan review (catatan dan form review)</small>
                        
                        <form action="{{ route('admin.submissions.update-process', $submission) }}" method="POST">
                            @csrf
                            <input type="hidden" name="step" value="reviewer2">
                            
                            <div class="mb-2">
                                <label class="form-label">Petugas Reviewer 2</label>
                                <select class="form-select form-select-sm" name="petugas_reviewer2_id" {{ $submission->reviewer2_valid ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Petugas --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $submission->petugas_reviewer2_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control form-control-sm" name="username_reviewer2" value="{{ $submission->username_reviewer2 }}" {{ $submission->reviewer2_valid ? 'disabled' : '' }}>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Password</label>
                                    <input type="text" class="form-control form-control-sm" name="password_reviewer2" value="{{ $submission->password_reviewer2 }}" {{ $submission->reviewer2_valid ? 'disabled' : '' }}>
                                </div>
                            </div>
                            @if(!$submission->reviewer2_valid)
                            <button type="submit" class="btn btn-sm btn-primary mt-2">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                            @endif
                        </form>
                        
                        <!-- Catatan Reviewer 2 -->
                        <form action="{{ route('admin.submissions.update-reviewer-notes', $submission) }}" method="POST" class="mt-2">
                            @csrf
                            <label class="form-label">Catatan Review</label>
                            <textarea class="form-control form-control-sm" name="catatan_reviewer2" rows="2">{{ $submission->catatan_reviewer2 }}</textarea>
                            <button type="submit" class="btn btn-sm btn-outline-primary mt-1">
                                <i class="bi bi-save"></i> Simpan Catatan
                            </button>
                        </form>
                        
                        @if(!$submission->reviewer2_valid && $submission->petugas_reviewer2_id && $submission->username_reviewer2 && $submission->reviewer1_valid)
                        <form action="{{ route('admin.submissions.validate-step', $submission) }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="step" value="reviewer2">
                            <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Validasi langkah ini?')">
                                <i class="bi bi-check-lg"></i> Validasi
                            </button>
                        </form>
                        @endif
                        
                        @if($submission->reviewer2_validated_at)
                        <small class="text-success mt-2 d-block">
                            <i class="bi bi-clock"></i> Divalidasi: {{ $submission->reviewer2_validated_at->format('d/m/Y H:i') }}
                        </small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 6: Editor 3 -->
            <div class="col-md-6 mb-4">
                <div class="card {{ $submission->editor3_valid ? 'border-success' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center {{ $submission->editor3_valid ? 'bg-success text-white' : '' }}">
                        <span>
                            <i class="bi {{ $submission->editor3_valid ? 'bi-check-circle-fill' : 'bi-6-circle' }}"></i>
                            Petugas Editor 3
                        </span>
                        @if($submission->editor3_valid)
                            <span class="badge bg-light text-success">Valid</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-2">Bertugas: Mengirimkan ke penulis revisi</small>
                        
                        <form action="{{ route('admin.submissions.update-process', $submission) }}" method="POST">
                            @csrf
                            <input type="hidden" name="step" value="editor3">
                            
                            <div class="mb-2">
                                <label class="form-label">Petugas Editor 3</label>
                                <select class="form-select form-select-sm" name="petugas_editor3_id" {{ $submission->editor3_valid ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Petugas --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $submission->petugas_editor3_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(!$submission->editor3_valid)
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                            @endif
                        </form>
                        
                        @if(!$submission->editor3_valid && $submission->petugas_editor3_id && $submission->reviewer2_valid)
                        <form action="{{ route('admin.submissions.validate-step', $submission) }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="step" value="editor3">
                            <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Validasi langkah ini?')">
                                <i class="bi bi-check-lg"></i> Validasi
                            </button>
                        </form>
                        @endif
                        
                        @if($submission->editor3_validated_at)
                        <small class="text-success mt-2 d-block">
                            <i class="bi bi-clock"></i> Divalidasi: {{ $submission->editor3_validated_at->format('d/m/Y H:i') }}
                        </small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 7: Author 2 -->
            <div class="col-md-6 mb-4">
                <div class="card {{ $submission->author2_valid ? 'border-success' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center {{ $submission->author2_valid ? 'bg-success text-white' : '' }}">
                        <span>
                            <i class="bi {{ $submission->author2_valid ? 'bi-check-circle-fill' : 'bi-7-circle' }}"></i>
                            Petugas Author 2
                        </span>
                        @if($submission->author2_valid)
                            <span class="badge bg-light text-success">Valid</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-2">Bertugas: Mengirimkan hasil revisi ke OJS</small>
                        
                        <form action="{{ route('admin.submissions.update-process', $submission) }}" method="POST">
                            @csrf
                            <input type="hidden" name="step" value="author2">
                            
                            <div class="mb-2">
                                <label class="form-label">Petugas Author 2</label>
                                <select class="form-select form-select-sm" name="petugas_author2_id" {{ $submission->author2_valid ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Petugas --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $submission->petugas_author2_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(!$submission->author2_valid)
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                            @endif
                        </form>
                        
                        @if(!$submission->author2_valid && $submission->petugas_author2_id && $submission->editor3_valid)
                        <form action="{{ route('admin.submissions.validate-step', $submission) }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="step" value="author2">
                            <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Validasi langkah ini?')">
                                <i class="bi bi-check-lg"></i> Validasi
                            </button>
                        </form>
                        @endif
                        
                        @if($submission->author2_validated_at)
                        <small class="text-success mt-2 d-block">
                            <i class="bi bi-clock"></i> Divalidasi: {{ $submission->author2_validated_at->format('d/m/Y H:i') }}
                        </small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 8: Production -->
            <div class="col-md-12 mb-4">
                <div class="card {{ $submission->production_valid ? 'border-success' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center {{ $submission->production_valid ? 'bg-success text-white' : '' }}">
                        <span>
                            <i class="bi {{ $submission->production_valid ? 'bi-check-circle-fill' : 'bi-8-circle' }}"></i>
                            Petugas Production
                        </span>
                        @if($submission->production_valid)
                            <span class="badge bg-light text-success">Valid - PUBLISHED</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-2">Bertugas: Editing dan publish</small>
                        
                        <form action="{{ route('admin.submissions.update-process', $submission) }}" method="POST">
                            @csrf
                            <input type="hidden" name="step" value="production">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label class="form-label">Petugas Production</label>
                                        <select class="form-select form-select-sm" name="petugas_production_id" {{ $submission->production_valid ? 'disabled' : '' }}>
                                            <option value="">-- Pilih Petugas --</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $submission->petugas_production_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label class="form-label">Link Publish</label>
                                        <input type="url" class="form-control form-control-sm" name="link_publish" value="{{ $submission->link_publish }}" placeholder="https://" {{ $submission->production_valid ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </div>
                            @if(!$submission->production_valid)
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                            @endif
                        </form>
                        
                        @if(!$submission->production_valid && $submission->petugas_production_id && $submission->author2_valid)
                        <form action="{{ route('admin.submissions.validate-step', $submission) }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="step" value="production">
                            <button type="submit" class="btn btn-success" onclick="return confirm('Validasi langkah ini? Artikel akan berubah status menjadi PUBLISHED.')">
                                <i class="bi bi-check-lg"></i> Validasi & Publish
                            </button>
                        </form>
                        @endif
                        
                        @if($submission->production_validated_at)
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="bi bi-check-circle-fill"></i> <strong>PUBLISHED!</strong><br>
                            Divalidasi: {{ $submission->production_validated_at->format('d/m/Y H:i') }}
                            @if($submission->link_publish)
                            <br><a href="{{ $submission->link_publish }}" target="_blank" class="text-success">{{ $submission->link_publish }} <i class="bi bi-box-arrow-up-right"></i></a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
