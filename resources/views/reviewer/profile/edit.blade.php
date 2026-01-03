@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Edit Profile Saya')

@section('sidebar')
    <a href="{{ route('reviewer.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('reviewer.tasks.index') }}" class="nav-link">
        <i class="bi bi-clipboard-check"></i> My Tasks
    </a>
    <a href="{{ route('reviewer.certificates.index') }}" class="nav-link">
        <i class="bi bi-award-fill"></i> Sertifikat
    </a>
    <a href="{{ route('reviewer.rewards.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Rewards
    </a>
    <a href="{{ route('reviewer.leaderboard.index') }}" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="{{ route('reviewer.profile.edit') }}" class="nav-link active">
        <i class="bi bi-person-circle"></i> My Profile
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-fill"></i> Lengkapi Biodata Reviewer
            </div>
            <div class="card-body">
                <form action="{{ route('reviewer.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Photo Upload -->
                    <div class="mb-4 text-center">
                        <div class="mb-3">
                            @if($user->photo)
                                <img src="{{ asset('storage/' . $user->photo) }}" 
                                     alt="Profile Photo" 
                                     class="rounded-circle"
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                     style="width: 150px; height: 150px; font-size: 3rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <input type="file" 
                               class="form-control w-50 mx-auto @error('photo') is-invalid @enderror" 
                               name="photo" 
                               accept="image/*">
                        @error('photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format: JPG, PNG (Max: 2MB)</small>
                    </div>

                    <hr>

                    <!-- Basic Info -->
                    <h5 class="mb-3"><i class="bi bi-info-circle"></i> Informasi Dasar</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               name="name" 
                               value="{{ old('name', $user->name) }}"
                               required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               name="email" 
                               value="{{ old('email', $user->email) }}"
                               required>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">No. Telepon/WhatsApp</label>
                        <input type="text" 
                               class="form-control @error('phone') is-invalid @enderror" 
                               name="phone" 
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="Contoh: 08123456789">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Academic Info -->
                    <h5 class="mb-3"><i class="bi bi-mortarboard-fill"></i> Informasi Akademik</h5>

                    <div class="mb-3">
                        <label class="form-label">Institusi/Universitas</label>
                        <input type="text" 
                               class="form-control @error('institution') is-invalid @enderror" 
                               name="institution" 
                               value="{{ old('institution', $user->institution) }}"
                               placeholder="Contoh: Universitas Indonesia">
                        @error('institution')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jabatan/Posisi</label>
                        <input type="text" 
                               class="form-control @error('position') is-invalid @enderror" 
                               name="position" 
                               value="{{ old('position', $user->position) }}"
                               placeholder="Contoh: Dosen, Lektor, Profesor">
                        @error('position')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pendidikan Terakhir</label>
                        <select class="form-select @error('education_level') is-invalid @enderror" name="education_level">
                            <option value="">Pilih Pendidikan</option>
                            <option value="S1" {{ old('education_level', $user->education_level) == 'S1' ? 'selected' : '' }}>S1</option>
                            <option value="S2" {{ old('education_level', $user->education_level) == 'S2' ? 'selected' : '' }}>S2</option>
                            <option value="S3" {{ old('education_level', $user->education_level) == 'S3' ? 'selected' : '' }}>S3</option>
                        </select>
                        @error('education_level')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">NIDN/NIDK</label>
                        <input type="text" 
                               class="form-control @error('nidn') is-invalid @enderror" 
                               name="nidn" 
                               value="{{ old('nidn', $user->nidn) }}"
                               placeholder="Nomor Induk Dosen Nasional">
                        @error('nidn')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bidang Spesialisasi</label>
                        <textarea class="form-control @error('specialization') is-invalid @enderror" 
                                  name="specialization" 
                                  rows="3"
                                  placeholder="Contoh: Artificial Intelligence, Data Science, Software Engineering">{{ old('specialization', $user->specialization) }}</textarea>
                        @error('specialization')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Pisahkan dengan koma jika lebih dari satu</small>
                    </div>

                    <hr>

                    <!-- Research Profile -->
                    <h5 class="mb-3"><i class="bi bi-search"></i> Profil Riset</h5>

                    <div class="mb-3">
                        <label class="form-label">Google Scholar</label>
                        <input type="url" 
                               class="form-control @error('google_scholar') is-invalid @enderror" 
                               name="google_scholar" 
                               value="{{ old('google_scholar', $user->google_scholar) }}"
                               placeholder="https://scholar.google.com/citations?user=...">
                        @error('google_scholar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">SINTA ID</label>
                        <input type="text" 
                               class="form-control @error('sinta_id') is-invalid @enderror" 
                               name="sinta_id" 
                               value="{{ old('sinta_id', $user->sinta_id) }}"
                               placeholder="Contoh: 6012345">
                        @error('sinta_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Scopus ID</label>
                        <input type="text" 
                               class="form-control @error('scopus_id') is-invalid @enderror" 
                               name="scopus_id" 
                               value="{{ old('scopus_id', $user->scopus_id) }}"
                               placeholder="Contoh: 57123456789">
                        @error('scopus_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Additional Info -->
                    <h5 class="mb-3"><i class="bi bi-file-text"></i> Informasi Tambahan</h5>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  name="address" 
                                  rows="3"
                                  placeholder="Alamat lengkap">{{ old('address', $user->address) }}</textarea>
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bio Singkat</label>
                        <textarea class="form-control @error('bio') is-invalid @enderror" 
                                  name="bio" 
                                  rows="4"
                                  placeholder="Ceritakan tentang Anda, pengalaman, dan minat riset Anda...">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Maksimal 1000 karakter</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanda Tangan Digital</label>
                        <div class="mb-2">
                            @if($user->signature)
                                <img src="{{ asset('storage/' . $user->signature) }}" 
                                     alt="Signature" 
                                     class="border rounded p-2 bg-white"
                                     style="max-width: 300px; max-height: 100px;">
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Belum ada tanda tangan digital
                                </div>
                            @endif
                        </div>
                        <input type="file" 
                               class="form-control @error('signature') is-invalid @enderror" 
                               name="signature" 
                               accept="image/*">
                        @error('signature')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format: PNG (background transparan direkomendasikan), Max: 1MB</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Simpan Profile
                        </button>
                        <a href="{{ route('reviewer.dashboard') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Stats Card -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-bar-chart-fill"></i> Statistik Anda
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Points:</span>
                    <strong class="text-primary">{{ number_format($user->total_points) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Available Points:</span>
                    <strong class="text-success">{{ number_format($user->available_points) }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Completed Reviews:</span>
                    <strong class="text-info">{{ $user->completed_reviews }}</strong>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Pentingnya Biodata
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Biodata lengkap meningkatkan kredibilitas Anda</li>
                    <li>Memudahkan admin untuk verifikasi</li>
                    <li>Mempermudah kolaborasi dengan reviewer lain</li>
                    <li>Profil riset membantu penugasan review yang sesuai</li>
                </ul>
            </div>
        </div>

        <!-- Tips Card -->
        <div class="card">
            <div class="card-header bg-warning">
                <i class="bi bi-lightbulb"></i> Tips Mengisi
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Gunakan nama lengkap sesuai identitas</li>
                    <li>Pastikan email aktif dan sering dicek</li>
                    <li>Isi spesialisasi sesuai keahlian</li>
                    <li>Link Google Scholar & SINTA membantu kredibilitas</li>
                    <li>Foto profil profesional lebih baik</li>
                </ul>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="card mt-3">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-key"></i> Ubah Password
            </div>
            <div class="card-body">
                <form action="{{ route('reviewer.profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if($errors->has('current_password') || $errors->has('new_password'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            @if($errors->has('current_password'))
                                {{ $errors->first('current_password') }}
                            @endif
                            @if($errors->has('new_password'))
                                {{ $errors->first('new_password') }}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="new_password" required minlength="8">
                        <small class="text-muted">Minimal 8 karakter</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="new_password_confirmation" required minlength="8">
                    </div>

                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-check-circle"></i> Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
