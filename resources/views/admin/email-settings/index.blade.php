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

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-octagon me-2"></i>
        <strong>Gagal menyimpan:</strong> {{ session('error') }}
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
                                       value="{{ old('mail_host', $emailSettings['mail_host'] ?? '') }}" 
                                       placeholder="smtp.gmail.com"
                                       required>
                                <small class="form-text text-muted">Contoh: smtp.gmail.com, smtp.titan.email</small>
                                @error('mail_host')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="smtpMismatchWarn" class="alert alert-warning py-1 px-2 mt-1 small d-none">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    <strong>Peringatan:</strong> <code>smtp.gmail.com</code> hanya untuk akun <strong>@gmail.com</strong> atau Google Workspace.
                                    Untuk email <strong>@apji.org</strong> gunakan SMTP hosting (mis: <code>mail.apji.org</code>).
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="mail_port" class="form-label">Port <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('mail_port') is-invalid @enderror" 
                                       id="mail_port" 
                                       name="mail_port" 
                                       value="{{ old('mail_port', $emailSettings['mail_port'] ?? '465') }}" 
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
                                    <option value="ssl"      {{ old('mail_encryption', $emailSettings['mail_encryption'] ?? 'ssl') == 'ssl'      ? 'selected' : '' }}>SSL (Port 465)</option>
                                    <option value="tls"      {{ old('mail_encryption', $emailSettings['mail_encryption'] ?? 'ssl') == 'tls'      ? 'selected' : '' }}>TLS (Port 587)</option>
                                    <option value="starttls" {{ old('mail_encryption', $emailSettings['mail_encryption'] ?? 'ssl') == 'starttls' ? 'selected' : '' }}>STARTTLS</option>
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
                                       value="{{ old('mail_username', $emailSettings['mail_username'] ?? '') }}" 
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
                                           value="{{ old('mail_password', $emailSettings['mail_password'] ?? '') }}" 
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
                                       value="{{ old('mail_from_address', $emailSettings['mail_from_address'] ?? '') }}" 
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
                                       value="{{ old('mail_from_name', $emailSettings['mail_from_name'] ?? '') }}" 
                                       placeholder="SIPERA System">
                                <small class="form-text text-muted">Nama pengirim yang akan muncul</small>
                                @error('mail_from_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2 mb-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#testEmailModal">
                                <i class="bi bi-send me-2"></i>Test Email
                            </button>
                        </div>
                    </form>

                    {{-- Panduan SMTP --}}
                    <div class="alert alert-light border mt-2">
                        <p class="fw-semibold mb-2"><i class="bi bi-lightbulb text-warning me-1"></i> Panduan Konfigurasi SMTP Umum:</p>
                        <table class="table table-sm table-bordered mb-0 small">
                            <thead class="table-light"><tr><th>Provider</th><th>Host</th><th>Port</th><th>Enkripsi</th><th>Username</th></tr></thead>
                            <tbody>
                                <tr><td>Gmail (App Password)</td><td>smtp.gmail.com</td><td>465</td><td>SSL</td><td>nama@gmail.com</td></tr>
                                <tr><td>Gmail (App Password)</td><td>smtp.gmail.com</td><td>587</td><td>TLS</td><td>nama@gmail.com</td></tr>
                                <tr class="table-warning"><td><strong>cPanel / Hosting</strong></td><td>mail.domainanda.com</td><td>465</td><td>SSL</td><td>email@domainanda.com</td></tr>
                                <tr><td>Titan Email</td><td>smtp.titan.email</td><td>465</td><td>SSL</td><td>nama@domainanda.com</td></tr>
                                <tr><td>Mailpit (lokal/dev)</td><td>localhost</td><td>1025</td><td>(kosong)</td><td>(kosong)</td></tr>
                            </tbody>
                        </table>
                        <p class="mb-0 mt-2 text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Perhatian:</strong> Untuk email domain sendiri (misal: sipera@apji.org), gunakan SMTP hosting Anda — bukan smtp.gmail.com.
                            Gmail hanya menerima <em>@gmail.com</em> atau akun Google Workspace.
                            Untuk Gmail, wajib gunakan <strong>App Password</strong> (bukan password biasa).
                        </p>
                    </div>
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

    // Warning jika smtp.gmail.com dipakai tapi username bukan @gmail.com
    function checkSmtpMismatch() {
        const host = (document.getElementById('mail_host').value || '').trim().toLowerCase();
        const user = (document.getElementById('mail_username').value || '').trim().toLowerCase();
        const warn = document.getElementById('smtpMismatchWarn');
        if (!warn) return;
        const isGmailHost = host === 'smtp.gmail.com';
        const isGmailUser = user.endsWith('@gmail.com') || user === '';
        warn.classList.toggle('d-none', !(isGmailHost && !isGmailUser));
    }
    ['mail_host','mail_username'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', checkSmtpMismatch);
    });
    checkSmtpMismatch();

    // Test email functionality
    document.getElementById('sendTestEmail').addEventListener('click', function() {
        const email = document.getElementById('test_email').value;
        const button = this;
        const resultDiv = document.getElementById('testEmailResult');

        if (!email) {
            resultDiv.innerHTML = '<div class="alert alert-danger">Silakan masukkan alamat email!</div>';
            return;
        }

        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
        resultDiv.innerHTML = '';

        fetch('{{ route("admin.email-settings.test-email") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())   // parse JSON regardless of status code
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML =
                    '<div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>' + data.message + '</div>';
            } else {
                let hint = '';
                const err = (data.error || '').toLowerCase();
                if (err.includes('authentication failed') || err.includes('535')) {
                    hint = '<hr class="my-2"><strong>💡 Kemungkinan penyebab:</strong><ul class="mb-0 mt-1">'
                         + '<li>Username atau password salah</li>'
                         + '<li>Menggunakan <code>smtp.gmail.com</code> dengan email non-Gmail (mis: @apji.org) → gunakan SMTP hosting Anda</li>'
                         + '<li>Untuk Gmail: wajib gunakan <strong>App Password</strong>, bukan password biasa</li>'
                         + '<li>Akses "Less Secure Apps" atau 2FA belum dikonfigurasi</li>'
                         + '</ul>';
                } else if (err.includes('connection') || err.includes('timeout') || err.includes('refused')) {
                    hint = '<hr class="my-2"><strong>💡 Kemungkinan penyebab:</strong><ul class="mb-0 mt-1">'
                         + '<li>Host SMTP salah atau tidak dapat dijangkau</li>'
                         + '<li>Port/enkripsi tidak cocok (coba port 587 + TLS atau 465 + SSL)</li>'
                         + '<li>Firewall server memblok koneksi keluar</li>'
                         + '</ul>';
                }
                resultDiv.innerHTML =
                    '<div class="alert alert-danger"><i class="bi bi-x-circle-fill me-2"></i><strong>' + data.message + '</strong>'
                    + '<br><small class="font-monospace">' + (data.error || '') + '</small>'
                    + hint + '</div>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML =
                '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Koneksi gagal: ' + error.message + '</div>';
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-send me-2"></i>Kirim Test Email';
        });
    });
</script>
@endpush
