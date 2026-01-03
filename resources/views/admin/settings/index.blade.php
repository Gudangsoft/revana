@extends('layouts.app')

@section('title', ' - Setting Web')
@section('page-title', 'Setting Web')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-gear-fill"></i> Pengaturan Aplikasi
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-info-circle"></i> Informasi Aplikasi
                        </h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Aplikasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('app_name') is-invalid @enderror" 
                                   name="app_name" value="{{ old('app_name', $settings['app_name'] ?? 'SIPERA') }}" required>
                            @error('app_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Nama singkat aplikasi (contoh: SIPERA)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kepanjangan Nama Aplikasi</label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                   name="full_name" value="{{ old('full_name', $settings['full_name'] ?? '') }}" 
                                   placeholder="Sistem Informasi Peer Review Artikel">
                            @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Kepanjangan lengkap dari nama aplikasi</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tagline</label>
                            <input type="text" class="form-control @error('tagline') is-invalid @enderror" 
                                   name="tagline" value="{{ old('tagline', $settings['tagline'] ?? '') }}" 
                                   placeholder="Sistem Manajemen Review Jurnal">
                            @error('tagline')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Tagline atau slogan aplikasi</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-image"></i> Logo & Favicon
                        </h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Logo Aplikasi</label>
                            @if($settings['logo'])
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $settings['logo']) }}" alt="Logo" style="max-height: 100px;" class="img-thumbnail">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                   name="logo" accept="image/*">
                            @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: JPG, PNG, SVG. Maksimal 2MB</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Favicon</label>
                            @if($settings['favicon'])
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $settings['favicon']) }}" alt="Favicon" style="max-height: 32px;" class="img-thumbnail">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('favicon') is-invalid @enderror" 
                                   name="favicon" accept="image/*">
                            @error('favicon')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: ICO, PNG. Maksimal 512KB. Ukuran disarankan: 32x32px</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-geo-alt"></i> Kontak & Alamat
                        </h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      name="address" rows="3" 
                                      placeholder="Jl. Contoh No. 123, Kota">{{ old('address', $settings['address'] ?? '') }}</textarea>
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Alamat lengkap organisasi/perusahaan</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kontak</label>
                            <textarea class="form-control @error('contact') is-invalid @enderror" 
                                      name="contact" rows="3" 
                                      placeholder="Telepon: +62 xxx&#10;Email: info@example.com&#10;WhatsApp: +62 xxx">{{ old('contact', $settings['contact'] ?? '') }}</textarea>
                            @error('contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Informasi kontak (telepon, email, WhatsApp, dll)</small>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="alert alert-warning mt-3">
            <i class="bi bi-exclamation-triangle"></i> 
            <strong>Perhatian:</strong> Perubahan pada nama aplikasi dan URL akan diterapkan setelah restart aplikasi.
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Informasi
            </div>
            <div class="card-body">
                <h6><i class="bi bi-shield-check"></i> Keamanan</h6>
                <p class="small text-muted">
                    Pengaturan ini akan mengubah file .env yang merupakan file konfigurasi penting. 
                    Pastikan Anda memahami dampak dari setiap perubahan.
                </p>
                
                <hr>
                
                <h6><i class="bi bi-arrow-clockwise"></i> Restart Diperlukan</h6>
                <p class="small text-muted">
                    Beberapa perubahan memerlukan restart aplikasi agar dapat diterapkan sepenuhnya.
                </p>
                
                <hr>
                
                <h6><i class="bi bi-gear"></i> Pengaturan Environment</h6>
                <ul class="small text-muted">
                    <li>APP_NAME: Nama aplikasi</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
