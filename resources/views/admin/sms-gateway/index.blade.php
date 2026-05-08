@extends('layouts.app')

@section('title', ' - Pengaturan SMS Gateway')
@section('page-title', 'Pengaturan SMS Gateway')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h1 class="h3 mb-0"><i class="bi bi-whatsapp text-success me-2"></i>Pengaturan SMS Gateway (Fonnte)</h1>
                <div class="d-flex gap-2">
                    <a href="https://fonnte.com" target="_blank" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Buka Fonnte Dashboard
                    </a>
                    <button type="submit" form="smsGatewayForm" class="btn btn-success btn-sm">
                        <i class="bi bi-save me-1"></i>Simpan Pengaturan
                    </button>
                </div>
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
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ session('error') }}
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

    <form action="{{ route('admin.sms-gateway.update') }}" method="POST" id="smsGatewayForm">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Konfigurasi API --}}
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-key me-2"></i>Konfigurasi API Fonnte</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Cara mendapatkan API Token:</strong>
                            <ol class="mb-0 mt-2">
                                <li>Buka <a href="https://fonnte.com" target="_blank" class="text-decoration-underline">fonnte.com</a> dan login/daftar</li>
                                <li>Tambahkan device (nomor WhatsApp) Anda</li>
                                <li>Hubungkan device dengan scan QR code</li>
                                <li>Salin API Token dari dashboard Fonnte</li>
                            </ol>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="fonnte_api_token" class="form-label">API Token <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control @error('fonnte_api_token') is-invalid @enderror"
                                           id="fonnte_api_token"
                                           name="fonnte_api_token"
                                           value="{{ old('fonnte_api_token', $settings['fonnte_api_token'] ?? '') }}"
                                           placeholder="Masukkan API Token dari Fonnte">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleToken">
                                        <i class="bi bi-eye" id="toggleTokenIcon"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" type="button" id="clearToken" title="Hapus token">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Token API dari dashboard Fonnte Anda</small>
                                @error('fonnte_api_token')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="fonnte_device_id" class="form-label">Device ID</label>
                                <input type="text"
                                       class="form-control @error('fonnte_device_id') is-invalid @enderror"
                                       id="fonnte_device_id"
                                       name="fonnte_device_id"
                                       value="{{ old('fonnte_device_id', $settings['fonnte_device_id'] ?? '') }}"
                                       placeholder="Opsional: ID Device di Fonnte">
                                <small class="form-text text-muted">Opsional, untuk identifikasi device</small>
                                @error('fonnte_device_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="sms_default_country_code" class="form-label">Kode Negara Default</label>
                                <input type="text"
                                       class="form-control @error('sms_default_country_code') is-invalid @enderror"
                                       id="sms_default_country_code"
                                       name="sms_default_country_code"
                                       value="{{ old('sms_default_country_code', $settings['sms_default_country_code'] ?? '62') }}"
                                       placeholder="62">
                                <small class="form-text text-muted">Kode negara Indonesia: 62</small>
                                @error('sms_default_country_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- Status Koneksi --}}
                        <div class="d-flex align-items-center gap-3">
                            <button type="button" class="btn btn-outline-success" id="checkStatusBtn">
                                <i class="bi bi-wifi me-1"></i>Cek Status Koneksi
                            </button>
                            <div id="statusResult"></div>
                        </div>
                    </div>
                </div>

                {{-- Notifikasi --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-bell me-2"></i>Pengaturan Notifikasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="sms_gateway_enabled" name="sms_gateway_enabled" value="1"
                                       {{ (old('sms_gateway_enabled', $settings['sms_gateway_enabled'] ?? '0')) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="sms_gateway_enabled">
                                    <i class="bi bi-power text-success me-1"></i>Aktifkan SMS Gateway
                                </label>
                            </div>
                            <small class="text-muted ms-5">Aktifkan untuk mengirim notifikasi WhatsApp otomatis</small>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="sms_notification_submit" name="sms_notification_submit" value="1"
                                       {{ (old('sms_notification_submit', $settings['sms_notification_submit'] ?? '0')) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="sms_notification_submit">
                                    <i class="bi bi-file-earmark-plus text-info me-1"></i>Notifikasi Submit Artikel
                                </label>
                            </div>
                            <small class="text-muted ms-5">Kirim WhatsApp saat artikel baru disubmit</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="sms_notification_status_change" name="sms_notification_status_change" value="1"
                                       {{ (old('sms_notification_status_change', $settings['sms_notification_status_change'] ?? '0')) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="sms_notification_status_change">
                                    <i class="bi bi-arrow-repeat text-warning me-1"></i>Notifikasi Perubahan Status
                                </label>
                            </div>
                            <small class="text-muted ms-5">Kirim WhatsApp saat status artikel berubah</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="sms_notification_published" name="sms_notification_published" value="1"
                                       {{ (old('sms_notification_published', $settings['sms_notification_published'] ?? '0')) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="sms_notification_published">
                                    <i class="bi bi-check-circle text-success me-1"></i>Notifikasi Artikel Terbit
                                </label>
                            </div>
                            <small class="text-muted ms-5">Kirim WhatsApp saat artikel berhasil terbit/published</small>
                        </div>
                    </div>
                </div>

                {{-- Template Pesan --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Template Pesan WhatsApp</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Variabel yang tersedia:</strong>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <code>{nama_penulis}</code> - Nama penulis<br>
                                    <code>{judul_artikel}</code> - Judul artikel<br>
                                    <code>{kode_submit}</code> - Kode submit<br>
                                </div>
                                <div class="col-md-6">
                                    <code>{status}</code> - Status saat ini<br>
                                    <code>{link_publish}</code> - Link publikasi<br>
                                    <code>{app_name}</code> - Nama aplikasi<br>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="sms_template_submit" class="form-label fw-bold mb-0">
                                    <i class="bi bi-file-earmark-plus text-info me-1"></i>Template Notifikasi Submit
                                </label>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-reset-template" data-target="sms_template_submit" data-default="Halo {nama_penulis},&#10;&#10;Artikel Anda &quot;{judul_artikel}&quot; telah berhasil disubmit dengan kode: {kode_submit}.&#10;&#10;Terima kasih,&#10;{app_name}">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset ke Default
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-clear-template" data-target="sms_template_submit">
                                        <i class="bi bi-trash me-1"></i>Hapus
                                    </button>
                                </div>
                            </div>
                            <textarea class="form-control @error('sms_template_submit') is-invalid @enderror"
                                      id="sms_template_submit"
                                      name="sms_template_submit"
                                      rows="5"
                                      placeholder="Template pesan saat artikel disubmit">{{ old('sms_template_submit', $settings['sms_template_submit'] ?? '') }}</textarea>
                            @error('sms_template_submit')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="sms_template_status_change" class="form-label fw-bold mb-0">
                                    <i class="bi bi-arrow-repeat text-warning me-1"></i>Template Perubahan Status
                                </label>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-reset-template" data-target="sms_template_status_change" data-default="Halo {nama_penulis},&#10;&#10;Status artikel &quot;{judul_artikel}&quot; ({kode_submit}) telah diupdate menjadi: {status}.&#10;&#10;Terima kasih,&#10;{app_name}">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset ke Default
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-clear-template" data-target="sms_template_status_change">
                                        <i class="bi bi-trash me-1"></i>Hapus
                                    </button>
                                </div>
                            </div>
                            <textarea class="form-control @error('sms_template_status_change') is-invalid @enderror"
                                      id="sms_template_status_change"
                                      name="sms_template_status_change"
                                      rows="5"
                                      placeholder="Template pesan saat status berubah">{{ old('sms_template_status_change', $settings['sms_template_status_change'] ?? '') }}</textarea>
                            @error('sms_template_status_change')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="sms_template_published" class="form-label fw-bold mb-0">
                                    <i class="bi bi-check-circle text-success me-1"></i>Template Artikel Terbit
                                </label>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-reset-template" data-target="sms_template_published" data-default="Halo {nama_penulis},&#10;&#10;Selamat! Artikel &quot;{judul_artikel}&quot; ({kode_submit}) telah berhasil dipublikasikan.&#10;&#10;Link: {link_publish}&#10;&#10;Terima kasih,&#10;{app_name}">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset ke Default
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-clear-template" data-target="sms_template_published">
                                        <i class="bi bi-trash me-1"></i>Hapus
                                    </button>
                                </div>
                            </div>
                            <textarea class="form-control @error('sms_template_published') is-invalid @enderror"
                                      id="sms_template_published"
                                      name="sms_template_published"
                                      rows="5"
                                      placeholder="Template pesan saat artikel terbit">{{ old('sms_template_published', $settings['sms_template_published'] ?? '') }}</textarea>
                            @error('sms_template_published')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Template Kredensial Penulis --}}
                        <hr class="my-4">
                        <h6 class="fw-bold text-success mb-3"><i class="bi bi-key me-2"></i>Template Notifikasi Kredensial Penulis</h6>
                        <div class="alert alert-info py-2 small">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Variabel:</strong>
                            <code>{nama}</code> <code>{kode}</code> <code>{judul}</code> <code>{namaJurnal}</code> <code>{linkSubmit}</code> <code>{username}</code> <code>{password}</code>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="wa_template_credential_new" class="form-label fw-bold mb-0">
                                    <i class="bi bi-person-plus text-success me-1"></i>Template Submission Baru (Kredensial Pertama)
                                </label>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-reset-template" data-target="wa_template_credential_new" data-default="{{ htmlspecialchars(\App\Http\Controllers\Admin\SmsGatewayController::defaultCredentialNewTemplate(), ENT_QUOTES) }}">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset ke Default
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-clear-template" data-target="wa_template_credential_new">
                                        <i class="bi bi-trash me-1"></i>Hapus
                                    </button>
                                </div>
                            </div>
                            <textarea class="form-control @error('wa_template_credential_new') is-invalid @enderror"
                                      id="wa_template_credential_new"
                                      name="wa_template_credential_new"
                                      rows="10"
                                      placeholder="Template pesan WA saat submission baru dibuat">{{ old('wa_template_credential_new', $settings['wa_template_credential_new'] ?? '') }}</textarea>
                            @error('wa_template_credential_new')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="wa_template_credential_update" class="form-label fw-bold mb-0">
                                    <i class="bi bi-pencil-square text-warning me-1"></i>Template Update Kredensial (Diperbarui)
                                </label>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-reset-template" data-target="wa_template_credential_update" data-default="{{ htmlspecialchars(\App\Http\Controllers\Admin\SmsGatewayController::defaultCredentialUpdateTemplate(), ENT_QUOTES) }}">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset ke Default
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-clear-template" data-target="wa_template_credential_update">
                                        <i class="bi bi-trash me-1"></i>Hapus
                                    </button>
                                </div>
                            </div>
                            <textarea class="form-control @error('wa_template_credential_update') is-invalid @enderror"
                                      id="wa_template_credential_update"
                                      name="wa_template_credential_update"
                                      rows="10"
                                      placeholder="Template pesan WA saat kredensial diperbarui">{{ old('wa_template_credential_update', $settings['wa_template_credential_update'] ?? '') }}</textarea>
                            @error('wa_template_credential_update')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar: Aksi & Test --}}
            <div class="col-lg-4">
                <div style="position: sticky; top: 80px;">
                {{-- Simpan --}}
                <div class="card shadow-sm mb-4 border-success">
                    <div class="card-header bg-success text-white py-2">
                        <strong><i class="bi bi-floppy me-1"></i>Aksi</strong>
                    </div>
                    <div class="card-body">
                        <button type="submit" class="btn btn-success w-100 mb-2">
                            <i class="bi bi-save me-2"></i>Simpan Pengaturan
                        </button>
                        <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#testSmsModal">
                            <i class="bi bi-send me-2"></i>Kirim Pesan Test
                        </button>
                    </div>
                </div>

                {{-- Panduan --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-question-circle me-2"></i>Panduan</h6>
                    </div>
                    <div class="card-body small">
                        <p class="mb-2"><strong>Fonnte</strong> adalah layanan WhatsApp Gateway yang memungkinkan pengiriman pesan WhatsApp melalui API.</p>
                        <hr>
                        <p class="mb-1"><strong>Langkah-langkah:</strong></p>
                        <ol class="ps-3 mb-0">
                            <li class="mb-1">Daftar di <a href="https://fonnte.com" target="_blank">fonnte.com</a></li>
                            <li class="mb-1">Tambahkan device dan hubungkan WhatsApp</li>
                            <li class="mb-1">Salin API Token</li>
                            <li class="mb-1">Paste token di form ini dan simpan</li>
                            <li class="mb-1">Cek status koneksi</li>
                            <li class="mb-1">Kirim pesan test untuk memastikan</li>
                        </ol>
                    </div>
                </div>

                {{-- Quick Info --}}
                <div class="card shadow-sm border-success">
                    <div class="card-body small">
                        <h6 class="text-success mb-3"><i class="bi bi-whatsapp me-2"></i>Keuntungan WhatsApp Gateway</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Notifikasi otomatis ke penulis</li>
                            <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Update status real-time</li>
                            <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Template pesan fleksibel</li>
                            <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Mendukung multi-nomor</li>
                            <li class="mb-0"><i class="bi bi-check2-circle text-success me-2"></i>Hemat waktu & profesional</li>
                        </ul>
                    </div>
                </div>
                </div>{{-- end sticky --}}
            </div>
        </div>

        {{-- Bottom action bar --}}
        <div class="row mt-2 mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body py-3 d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Semua perubahan akan tersimpan setelah klik tombol Simpan.</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#testSmsModal">
                                <i class="bi bi-send me-1"></i>Kirim Pesan Test
                            </button>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-save me-1"></i>Simpan Pengaturan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Test SMS Modal -->
<div class="modal fade" id="testSmsModal" tabindex="-1" aria-labelledby="testSmsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="testSmsModalLabel">
                    <i class="bi bi-whatsapp me-2"></i>Kirim Pesan Test WhatsApp
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <small>Pastikan Anda sudah menyimpan pengaturan dan device WhatsApp sudah terhubung!</small>
                </div>
                <div class="mb-3">
                    <label for="test_phone" class="form-label">Nomor WhatsApp Penerima</label>
                    <input type="text" class="form-control" id="test_phone" placeholder="08123456789 atau 628123456789">
                    <small class="form-text text-muted">Masukkan nomor dengan atau tanpa kode negara</small>
                </div>
                <div class="mb-3">
                    <label for="test_message" class="form-label">Pesan</label>
                    <textarea class="form-control" id="test_message" rows="3" placeholder="Tulis pesan test...">Ini adalah pesan test dari sistem {{ config('app.name') }}. Jika Anda menerima pesan ini, berarti konfigurasi WhatsApp Gateway berhasil! ✅</textarea>
                </div>
                <div id="testSmsResult"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" id="sendTestSms">
                    <i class="bi bi-send me-2"></i>Kirim Test
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle token visibility
    document.getElementById('toggleToken').addEventListener('click', function() {
        const tokenInput = document.getElementById('fonnte_api_token');
        const icon = document.getElementById('toggleTokenIcon');

        if (tokenInput.type === 'password') {
            tokenInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            tokenInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });

    // Hapus / clear API token
    document.getElementById('clearToken').addEventListener('click', function() {
        if (confirm('Hapus API Token? Gateway tidak akan berfungsi sampai token baru diisi dan disimpan.')) {
            document.getElementById('fonnte_api_token').value = '';
            document.getElementById('fonnte_api_token').type = 'text';
            document.getElementById('toggleTokenIcon').classList.remove('bi-eye-slash');
            document.getElementById('toggleTokenIcon').classList.add('bi-eye');
        }
    });

    // Reset template ke default
    document.querySelectorAll('.btn-reset-template').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const target = this.dataset.target;
            const defaultText = this.dataset.default
                .replace(/&quot;/g, '"')
                .replace(/&#10;/g, '\n');
            document.getElementById(target).value = defaultText;
        });
    });

    // Hapus (kosongkan) template
    document.querySelectorAll('.btn-clear-template').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const target = this.dataset.target;
            if (confirm('Kosongkan template ini? Perubahan baru berlaku setelah disimpan.')) {
                document.getElementById(target).value = '';
            }
        });
    });

    // Check connection status
    document.getElementById('checkStatusBtn').addEventListener('click', function() {
        const button = this;
        const resultDiv = document.getElementById('statusResult');
        const token = document.getElementById('fonnte_api_token').value;

        if (!token) {
            resultDiv.innerHTML = '<span class="badge bg-warning text-dark p-2"><i class="bi bi-exclamation-triangle me-1"></i>Masukkan API Token terlebih dahulu</span>';
            return;
        }

        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengecek...';
        resultDiv.innerHTML = '';

        fetch('{{ route("admin.sms-gateway.check-status") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ token: token })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<span class="badge bg-success p-2"><i class="bi bi-check-circle me-1"></i>Terhubung</span>';
            } else {
                resultDiv.innerHTML = '<span class="badge bg-danger p-2"><i class="bi bi-x-circle me-1"></i>' + (data.message || 'Tidak terhubung') + '</span>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<span class="badge bg-danger p-2"><i class="bi bi-x-circle me-1"></i>Error: ' + error.message + '</span>';
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-wifi me-1"></i>Cek Status Koneksi';
        });
    });

    // Send test SMS
    document.getElementById('sendTestSms').addEventListener('click', function() {
        const phone = document.getElementById('test_phone').value;
        const message = document.getElementById('test_message').value;
        const button = this;
        const resultDiv = document.getElementById('testSmsResult');

        if (!phone) {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Silakan masukkan nomor telepon!</div>';
            return;
        }

        if (!message) {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Silakan tulis pesan!</div>';
            return;
        }

        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
        resultDiv.innerHTML = '';

        const token = document.getElementById('fonnte_api_token').value;

        fetch('{{ route("admin.sms-gateway.test-send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ phone: phone, message: message, token: token })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>' + (data.message || 'Pesan berhasil dikirim!') + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>' + (data.message || 'Gagal mengirim pesan') + '</div>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Error: ' + error.message + '</div>';
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-send me-2"></i>Kirim Test';
        });
    });
</script>
@endpush
