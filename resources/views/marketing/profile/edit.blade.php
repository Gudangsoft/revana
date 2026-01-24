@extends('marketing.layouts.app')

@section('title', 'Profile Saya')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <h4 class="mb-4">
            <i class="bi bi-person-circle"></i> Profile Saya
        </h4>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Profile Information Card -->
        <div class="card mb-4">
            <div class="card-header" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                <i class="bi bi-person-circle"></i> Informasi Profile
            </div>
            <div class="card-body">
                <form action="{{ route('marketing.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row mb-4">
                        <div class="col-md-12 text-center">
                            <div class="mb-3">
                                @if($marketing->photo)
                                    <img src="{{ asset('storage/' . $marketing->photo) }}" 
                                         alt="Photo" 
                                         class="rounded-circle" 
                                         style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #11998e;"
                                         id="preview-photo">
                                @else
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center" 
                                         style="width: 150px; height: 150px; border: 4px solid #11998e; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                                        <i class="bi bi-person-fill text-white" style="font-size: 4rem;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="mb-2">
                                <label for="photo" class="btn btn-sm" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                                    <i class="bi bi-camera"></i> Pilih Foto
                                </label>
                                <input type="file" 
                                       class="d-none @error('photo') is-invalid @enderror" 
                                       id="photo" 
                                       name="photo" 
                                       accept="image/jpeg,image/jpg,image/png"
                                       onchange="previewImage(event)">
                                @error('photo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $marketing->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('username') is-invalid @enderror" 
                                       id="username" 
                                       name="username" 
                                       value="{{ old('username', $marketing->username) }}" 
                                       required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $marketing->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">No. Telepon</label>
                                <input type="text" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', $marketing->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('marketing.dashboard') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <button type="submit" class="btn text-white" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-shield-lock"></i> Ubah Password
            </div>
            <div class="card-body">
                <form action="{{ route('marketing.profile.update-password') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" 
                               name="current_password" 
                               required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control @error('new_password') is-invalid @enderror" 
                               id="new_password" 
                               name="new_password" 
                               required>
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>

                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control" 
                               id="new_password_confirmation" 
                               name="new_password_confirmation" 
                               required>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-shield-check"></i> Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('preview-photo');
            if (preview) {
                preview.src = e.target.result;
            } else {
                // Create new image if doesn't exist
                const container = event.target.closest('.col-md-12').querySelector('.mb-3');
                container.innerHTML = `<img src="${e.target.result}" 
                                           alt="Photo" 
                                           class="rounded-circle" 
                                           style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #11998e;"
                                           id="preview-photo">`;
            }
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
