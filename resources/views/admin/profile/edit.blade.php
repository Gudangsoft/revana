@extends('layouts.app')

@section('title', ' - Edit Profile Admin')
@section('page-title', 'Edit Profile Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Profile Information Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-circle"></i> Informasi Profile
                </h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any() && !$errors->has('current_password') && !$errors->has('new_password'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Photo Upload -->
                    <div class="mb-4 text-center">
                        <label class="form-label d-block">Foto Profile</label>
                        @if($user->photo)
                            <img src="{{ asset('storage/' . $user->photo) }}" alt="Profile Photo" class="rounded-circle mb-3" width="150" height="150" style="object-fit: cover; border: 3px solid #4f46e5;">
                        @else
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px; border: 3px solid #4f46e5;">
                                <i class="bi bi-person-fill text-white" style="font-size: 4rem;"></i>
                            </div>
                        @endif
                        <div>
                            <input type="file" class="form-control w-auto d-inline-block" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg">
                            <small class="text-muted d-block mt-1">Format: JPG, PNG (Max: 2MB)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">
                                <i class="bi bi-whatsapp text-success"></i> Nomor WhatsApp
                            </label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                   placeholder="08123456789 atau 6281234567890">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: 08xxx atau 628xxx</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" class="form-control" id="role" value="Admin" readonly>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-key-fill"></i> Ubah Password
                </h5>
            </div>
            <div class="card-body">
                @if($errors->has('current_password') || $errors->has('new_password'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <ul class="mb-0">
                            @error('current_password')
                                <li>{{ $message }}</li>
                            @enderror
                            @error('new_password')
                                <li>{{ $message }}</li>
                            @enderror
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('admin.profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                               id="new_password" name="new_password" required>
                        <small class="text-muted">Minimal 8 karakter</small>
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" 
                               id="new_password_confirmation" name="new_password_confirmation" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-shield-lock"></i> Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Account Info Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Informasi Akun
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Role</label>
                    <p class="mb-0"><span class="badge bg-danger">Administrator</span></p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Email</label>
                    <p class="mb-0">{{ $user->email }}</p>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Nama</label>
                    <p class="mb-0">{{ $user->name }}</p>
                </div>

                @if($user->phone)
                <div class="mb-3">
                    <label class="text-muted small">Telepon</label>
                    <p class="mb-0">{{ $user->phone }}</p>
                </div>
                @endif

                <div class="mb-0">
                    <label class="text-muted small">Akun Dibuat</label>
                    <p class="mb-0">{{ $user->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Quick Tips Card -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightbulb"></i> Tips Keamanan
                </h5>
            </div>
            <div class="card-body">
                <ul class="mb-0 ps-3">
                    <li class="mb-2">Gunakan password yang kuat dengan kombinasi huruf, angka, dan simbol</li>
                    <li class="mb-2">Jangan bagikan password Anda kepada siapa pun</li>
                    <li class="mb-2">Ubah password secara berkala untuk keamanan</li>
                    <li class="mb-0">Pastikan email yang terdaftar aktif dan dapat diakses</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        border-radius: 10px 10px 0 0 !important;
        padding: 1rem 1.5rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
    }

    .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border: none;
        color: white;
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
    }
</style>
@endsection
