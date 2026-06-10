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

        @if(!$marketing->tanggal_lahir || !$marketing->email)
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>Lengkapi profil Anda: <strong>Tanggal Lahir</strong> dan <strong>Gmail aktif</strong> wajib diisi agar bisa menerima ucapan ulang tahun.</div>
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
                                <label for="email" class="form-label">
                                    Gmail Aktif <span class="text-danger">*</span>
                                    <i class="bi bi-google text-danger ms-1" title="Harus Gmail (@gmail.com)"></i>
                                </label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $marketing->email) }}"
                                       placeholder="nama@gmail.com"
                                       required>
                                <div class="form-text text-muted"><i class="bi bi-envelope-check"></i> Pastikan Gmail aktif dan bisa menerima email</div>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">No. Telepon / WhatsApp</label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $marketing->phone) }}"
                                       placeholder="628xxx">
                                <div class="form-text text-muted"><i class="bi bi-whatsapp text-success"></i> Format: 628xxx. Digunakan untuk notifikasi WhatsApp.</div>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal_lahir" class="form-label">
                                    Tanggal Lahir <span class="text-danger">*</span>
                                    <i class="bi bi-cake2 text-warning ms-1"></i>
                                </label>
                                <input type="date"
                                       class="form-control @error('tanggal_lahir') is-invalid @enderror"
                                       id="tanggal_lahir"
                                       name="tanggal_lahir"
                                       value="{{ old('tanggal_lahir', $marketing->tanggal_lahir?->format('Y-m-d')) }}"
                                       max="{{ now()->subDay()->format('Y-m-d') }}"
                                       required>
                                <div class="form-text text-muted"><i class="bi bi-calendar-check"></i> Pastikan data lahir terisi dengan benar</div>
                                @error('tanggal_lahir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @if($marketing->tanggal_lahir)
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="alert alert-info py-2 w-100 mb-0" style="border-left: 4px solid #11998e;">
                                <i class="bi bi-cake2-fill text-warning"></i>
                                Ulang tahun ke-<strong>{{ $marketing->umur }}</strong> pada
                                <strong>{{ $marketing->tanggal_lahir->locale('id')->translatedFormat('j F') }}</strong>
                                @if($marketing->isBirthdayToday()) <span class="badge bg-warning text-dark ms-1">🎉 Hari ini!</span> @endif
                            </div>
                        </div>
                        @endif
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
