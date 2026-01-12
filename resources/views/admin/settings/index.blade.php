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

                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-envelope"></i> Pengaturan Email (SMTP)
                        </h5>
                        
                        <div class="mb-3">
                            <label class="form-label">SMTP Host <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('mail_host') is-invalid @enderror" 
                                   name="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? '') }}" 
                                   placeholder="smtp.gmail.com" required>
                            @error('mail_host')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Host server SMTP (contoh: smtp.titan.email)</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SMTP Port <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('mail_port') is-invalid @enderror" 
                                       name="mail_port" value="{{ old('mail_port', $settings['mail_port'] ?? '465') }}" 
                                       placeholder="465" required>
                                @error('mail_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Port SMTP (465 untuk SSL, 587 untuk TLS)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Encryption <span class="text-danger">*</span></label>
                                <select class="form-select @error('mail_encryption') is-invalid @enderror" 
                                        name="mail_encryption" required>
                                    <option value="ssl" {{ old('mail_encryption', $settings['mail_encryption'] ?? 'ssl') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="tls" {{ old('mail_encryption', $settings['mail_encryption'] ?? 'ssl') == 'tls' ? 'selected' : '' }}>TLS</option>
                                </select>
                                @error('mail_encryption')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">SMTP Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('mail_username') is-invalid @enderror" 
                                   name="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" 
                                   placeholder="user@example.com" required>
                            @error('mail_username')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Username/email untuk autentikasi SMTP</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">SMTP Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('mail_password') is-invalid @enderror" 
                                   name="mail_password" value="{{ old('mail_password', $settings['mail_password'] ?? '') }}" 
                                   placeholder="••••••••" required>
                            @error('mail_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Password untuk autentikasi SMTP</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">From Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('mail_from_address') is-invalid @enderror" 
                                   name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}" 
                                   placeholder="noreply@example.com" required>
                            @error('mail_from_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Email pengirim untuk notifikasi</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">From Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('mail_from_name') is-invalid @enderror" 
                                   name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? '') }}" 
                                   placeholder="SIPERA" required>
                            @error('mail_from_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Nama pengirim untuk notifikasi</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Test Email:</strong> Setelah menyimpan, gunakan tombol "Test Email" untuk memastikan konfigurasi berhasil.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                        <button type="button" class="btn btn-success" id="testEmailBtn">
                            <i class="bi bi-envelope-check"></i> Test Email
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Test Email Modal -->
        <div class="modal fade" id="testEmailModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Test Pengiriman Email</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Email Tujuan <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="testEmailTo" 
                                   placeholder="email@example.com" required>
                            <small class="text-muted">Masukkan email untuk menerima test email</small>
                        </div>
                        <div id="testEmailResult"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" id="sendTestEmailBtn">
                            <i class="bi bi-send"></i> Kirim Test Email
                        </button>
                    </div>
                </div>
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

@push('scripts')
<script>
document.getElementById('testEmailBtn').addEventListener('click', function() {
    var modal = new bootstrap.Modal(document.getElementById('testEmailModal'));
    modal.show();
});

document.getElementById('sendTestEmailBtn').addEventListener('click', function() {
    const btn = this;
    const originalHtml = btn.innerHTML;
    const emailTo = document.getElementById('testEmailTo').value;
    const resultDiv = document.getElementById('testEmailResult');
    
    if (!emailTo) {
        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Email tujuan harus diisi!</div>';
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengirim...';
    resultDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Sedang mengirim email test...</div>';
    
    fetch('{{ route("admin.settings.test-email") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ email: emailTo })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> ${data.message}
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> ${data.message}
                    ${data.error ? '<br><small class="mt-2 d-block">' + data.error + '</small>' : ''}
                </div>
            `;
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> Terjadi kesalahan: ${error.message}
            </div>
        `;
    });
});
</script>
@endpush
@endsection
