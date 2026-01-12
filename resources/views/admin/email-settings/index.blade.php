@extends('layouts.app')

@section('title', ' - Pengaturan Email')
@section('page-title', 'Pengaturan Email')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Pengaturan Email</h1>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-envelope-at me-2"></i>Konfigurasi SMTP</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.email-settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Informasi:</strong> Konfigurasi ini digunakan untuk pengiriman email notifikasi reviewer, approval, dan revision.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="mail_host" class="form-label">SMTP Host <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('mail_host') is-invalid @enderror" 
                                       id="mail_host" 
                                       name="mail_host" 
                                       value="{{ old('mail_host', $settings['mail_host'] ?? '') }}" 
                                       placeholder="smtp.gmail.com"
                                       required>
                                <small class="form-text text-muted">Contoh: smtp.gmail.com, smtp.titan.email</small>
                                @error('mail_host')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="mail_port" class="form-label">Port <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('mail_port') is-invalid @enderror" 
                                       id="mail_port" 
                                       name="mail_port" 
                                       value="{{ old('mail_port', $settings['mail_port'] ?? '465') }}" 
                                       placeholder="465"
                                       min="1"
                                       max="65535"
                                       required>
                                <small class="form-text text-muted">Biasanya 465 atau 587</small>
                                @error('mail_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="mail_encryption" class="form-label">Enkripsi <span class="text-danger">*</span></label>
                                <select class="form-select @error('mail_encryption') is-invalid @enderror" 
                                        id="mail_encryption" 
                                        name="mail_encryption" 
                                        required>
                                    <option value="ssl" {{ old('mail_encryption', $settings['mail_encryption'] ?? 'ssl') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="tls" {{ old('mail_encryption', $settings['mail_encryption'] ?? 'ssl') == 'tls' ? 'selected' : '' }}>TLS</option>
                                </select>
                                @error('mail_encryption')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="mail_username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('mail_username') is-invalid @enderror" 
                                       id="mail_username" 
                                       name="mail_username" 
                                       value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" 
                                       placeholder="user@example.com"
                                       required>
                                <small class="form-text text-muted">Biasanya alamat email Anda</small>
                                @error('mail_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="mail_password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('mail_password') is-invalid @enderror" 
                                           id="mail_password" 
                                           name="mail_password" 
                                           value="{{ old('mail_password', $settings['mail_password'] ?? '') }}" 
                                           placeholder="••••••••"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Password email atau App Password</small>
                                @error('mail_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="mail_from_address" class="form-label">Alamat Pengirim</label>
                                <input type="email" 
                                       class="form-control @error('mail_from_address') is-invalid @enderror" 
                                       id="mail_from_address" 
                                       name="mail_from_address" 
                                       value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}" 
                                       placeholder="noreply@example.com">
                                <small class="form-text text-muted">Email pengirim yang akan muncul</small>
                                @error('mail_from_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="mail_from_name" class="form-label">Nama Pengirim</label>
                                <input type="text" 
                                       class="form-control @error('mail_from_name') is-invalid @enderror" 
                                       id="mail_from_name" 
                                       name="mail_from_name" 
                                       value="{{ old('mail_from_name', $settings['mail_from_name'] ?? '') }}" 
                                       placeholder="SIPERA System">
                                <small class="form-text text-muted">Nama pengirim yang akan muncul</small>
                                @error('mail_from_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#testEmailModal">
                                <i class="bi bi-send me-2"></i>Test Email
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1" aria-labelledby="testEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testEmailModalLabel">
                    <i class="bi bi-send me-2"></i>Test Pengiriman Email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <small>Pastikan Anda sudah menyimpan pengaturan email sebelum melakukan test!</small>
                </div>
                <div class="mb-3">
                    <label for="test_email" class="form-label">Email Penerima</label>
                    <input type="email" class="form-control" id="test_email" placeholder="your@email.com" required>
                </div>
                <div id="testEmailResult"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="sendTestEmail">
                    <i class="bi bi-send me-2"></i>Kirim Test Email
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('mail_password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        }
    });

    // Test email functionality
    document.getElementById('sendTestEmail').addEventListener('click', function() {
        const email = document.getElementById('test_email').value;
        const button = this;
        const resultDiv = document.getElementById('testEmailResult');
        
        if (!email) {
            resultDiv.innerHTML = '<div class="alert alert-danger">Silakan masukkan alamat email!</div>';
            return;
        }
        
        // Disable button and show loading
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
        resultDiv.innerHTML = '';
        
        // Send test email
        fetch('{{ route("admin.email-settings.test-email") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error('Server error: ' + (text || response.statusText));
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>' + data.message + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>' + data.message + '<br><small>' + (data.error || '') + '</small></div>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Terjadi kesalahan: ' + error.message + '</div>';
        })
        .finally(() => {
            // Re-enable button
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-send me-2"></i>Kirim Test Email';
        });
    });
</script>
@endpush
