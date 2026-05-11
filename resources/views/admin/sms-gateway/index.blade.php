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

    {{-- Banner status — ditampilkan/disembunyikan via JS berdasarkan localStorage --}}
    <div id="bannerConfigured" class="alert alert-success align-items-start gap-3 mb-4 d-none"
         style="border-left:4px solid #198754; display:none;">
        <i class="bi bi-check-circle-fill fs-5 mt-1"></i>
        <div>
            <strong>Pengaturan aktif saat ini:</strong>
            <div class="row mt-1 g-2" id="bannerBadges"></div>
        </div>
    </div>
    <div id="bannerUnconfigured" class="alert alert-warning align-items-center gap-2 mb-4 d-none"
         style="display:none;">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span>Pengaturan belum dikonfigurasi. Isi API Token dan simpan untuk mengaktifkan notifikasi WhatsApp.</span>
    </div>

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
                                    <input type="text"
                                           class="form-control font-monospace @error('fonnte_api_token') is-invalid @enderror"
                                           id="fonnte_api_token"
                                           name="fonnte_api_token"
                                           value="{{ old('fonnte_api_token', $settings['fonnte_api_token'] ?? '') }}"
                                           placeholder="Masukkan API Token dari Fonnte"
                                           autocomplete="off">
                                    <button class="btn btn-outline-danger" type="button" id="clearToken" title="Hapus token">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <div class="mt-1" id="tokenStatusWrap">
                                    <small id="tokenStatus"></small>
                                </div>
                                @error('fonnte_api_token')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
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
                                @if(!empty($settings['fonnte_device_id']))
                                    <small class="text-success"><i class="bi bi-check-circle me-1"></i>Tersimpan: <strong>{{ $settings['fonnte_device_id'] }}</strong></small>
                                @else
                                    <small class="text-muted">Opsional, untuk identifikasi device</small>
                                @endif
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
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input toggle-setting" type="checkbox" role="switch"
                                           id="sms_gateway_enabled" name="sms_gateway_enabled" value="1"
                                           {{ (old('sms_gateway_enabled', $settings['sms_gateway_enabled'] ?? '0')) == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="sms_gateway_enabled">
                                        <i class="bi bi-power text-success me-1"></i>Aktifkan SMS Gateway
                                    </label>
                                </div>
                                <span class="toggle-badge" data-for="sms_gateway_enabled"></span>
                            </div>
                            <small class="text-muted ms-5">Aktifkan untuk mengirim notifikasi WhatsApp otomatis</small>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input toggle-setting" type="checkbox" role="switch"
                                           id="sms_notification_submit" name="sms_notification_submit" value="1"
                                           {{ (old('sms_notification_submit', $settings['sms_notification_submit'] ?? '0')) == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sms_notification_submit">
                                        <i class="bi bi-file-earmark-plus text-info me-1"></i>Notifikasi Submit Artikel
                                    </label>
                                </div>
                                <span class="toggle-badge" data-for="sms_notification_submit"></span>
                            </div>
                            <small class="text-muted ms-5">Kirim WhatsApp saat artikel baru disubmit</small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input toggle-setting" type="checkbox" role="switch"
                                           id="sms_notification_status_change" name="sms_notification_status_change" value="1"
                                           {{ (old('sms_notification_status_change', $settings['sms_notification_status_change'] ?? '0')) == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sms_notification_status_change">
                                        <i class="bi bi-arrow-repeat text-warning me-1"></i>Notifikasi Perubahan Status
                                    </label>
                                </div>
                                <span class="toggle-badge" data-for="sms_notification_status_change"></span>
                            </div>
                            <small class="text-muted ms-5">Kirim WhatsApp saat status artikel berubah</small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input toggle-setting" type="checkbox" role="switch"
                                           id="sms_notification_published" name="sms_notification_published" value="1"
                                           {{ (old('sms_notification_published', $settings['sms_notification_published'] ?? '0')) == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sms_notification_published">
                                        <i class="bi bi-check-circle text-success me-1"></i>Notifikasi Artikel Terbit
                                    </label>
                                </div>
                                <span class="toggle-badge" data-for="sms_notification_published"></span>
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
                            <textarea class="form-control template-textarea @error('sms_template_submit') is-invalid @enderror"
                                      id="sms_template_submit"
                                      name="sms_template_submit"
                                      rows="5"
                                      placeholder="Template pesan saat artikel disubmit">{{ old('sms_template_submit', $settings['sms_template_submit'] ?? '') }}</textarea>
                            <div class="d-flex justify-content-between mt-1">
                                @if(!empty($settings['sms_template_submit']))
                                    <small class="text-success"><i class="bi bi-check-circle me-1"></i>Tersimpan · {{ mb_strlen($settings['sms_template_submit']) }} karakter</small>
                                @else
                                    <small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Kosong — template belum diisi</small>
                                @endif
                                <small class="text-muted char-counter" data-target="sms_template_submit"></small>
                            </div>
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
                            <textarea class="form-control template-textarea @error('sms_template_status_change') is-invalid @enderror"
                                      id="sms_template_status_change"
                                      name="sms_template_status_change"
                                      rows="5"
                                      placeholder="Template pesan saat status berubah">{{ old('sms_template_status_change', $settings['sms_template_status_change'] ?? '') }}</textarea>
                            <div class="d-flex justify-content-between mt-1">
                                @if(!empty($settings['sms_template_status_change']))
                                    <small class="text-success"><i class="bi bi-check-circle me-1"></i>Tersimpan · {{ mb_strlen($settings['sms_template_status_change']) }} karakter</small>
                                @else
                                    <small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Kosong — template belum diisi</small>
                                @endif
                                <small class="text-muted char-counter" data-target="sms_template_status_change"></small>
                            </div>
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
                            <textarea class="form-control template-textarea @error('sms_template_published') is-invalid @enderror"
                                      id="sms_template_published"
                                      name="sms_template_published"
                                      rows="5"
                                      placeholder="Template pesan saat artikel terbit">{{ old('sms_template_published', $settings['sms_template_published'] ?? '') }}</textarea>
                            <div class="d-flex justify-content-between mt-1">
                                @if(!empty($settings['sms_template_published']))
                                    <small class="text-success"><i class="bi bi-check-circle me-1"></i>Tersimpan · {{ mb_strlen($settings['sms_template_published']) }} karakter</small>
                                @else
                                    <small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Kosong — template belum diisi</small>
                                @endif
                                <small class="text-muted char-counter" data-target="sms_template_published"></small>
                            </div>
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
                            <textarea class="form-control template-textarea @error('wa_template_credential_new') is-invalid @enderror"
                                      id="wa_template_credential_new"
                                      name="wa_template_credential_new"
                                      rows="10"
                                      placeholder="Template pesan WA saat submission baru dibuat">{{ old('wa_template_credential_new', $settings['wa_template_credential_new'] ?? '') }}</textarea>
                            <div class="d-flex justify-content-between mt-1">
                                @if(!empty($settings['wa_template_credential_new']))
                                    <small class="text-success"><i class="bi bi-check-circle me-1"></i>Tersimpan · {{ mb_strlen($settings['wa_template_credential_new']) }} karakter</small>
                                @else
                                    <small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Kosong — template belum diisi</small>
                                @endif
                                <small class="text-muted char-counter" data-target="wa_template_credential_new"></small>
                            </div>
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
                            <textarea class="form-control template-textarea @error('wa_template_credential_update') is-invalid @enderror"
                                      id="wa_template_credential_update"
                                      name="wa_template_credential_update"
                                      rows="10"
                                      placeholder="Template pesan WA saat kredensial diperbarui">{{ old('wa_template_credential_update', $settings['wa_template_credential_update'] ?? '') }}</textarea>
                            <div class="d-flex justify-content-between mt-1">
                                @if(!empty($settings['wa_template_credential_update']))
                                    <small class="text-success"><i class="bi bi-check-circle me-1"></i>Tersimpan · {{ mb_strlen($settings['wa_template_credential_update']) }} karakter</small>
                                @else
                                    <small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Kosong — template belum diisi</small>
                                @endif
                                <small class="text-muted char-counter" data-target="wa_template_credential_update"></small>
                            </div>
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
    // Hapus / clear API token
    document.getElementById('clearToken').addEventListener('click', function() {
        if (confirm('Hapus API Token? Gateway tidak akan berfungsi sampai token baru diisi dan disimpan.')) {
            document.getElementById('fonnte_api_token').value = '';
        }
    });

    // Token status badge — baca dari isi input langsung (tidak bergantung PHP $settings)
    function updateTokenStatus() {
        const val = document.getElementById('fonnte_api_token').value.trim();
        const el  = document.getElementById('tokenStatus');
        if (!el) return;
        if (val.length > 0) {
            el.className = 'text-success';
            el.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Token terisi &mdash; ' + val.length + ' karakter. Kosongkan hanya jika ingin mengganti token.';
        } else {
            el.className = 'text-danger';
            el.innerHTML = '<i class="bi bi-x-circle me-1"></i>Belum diisi &mdash; masukkan API Token dari dashboard Fonnte lalu simpan.';
        }
    }
    document.getElementById('fonnte_api_token').addEventListener('input', updateTokenStatus);
    updateTokenStatus();

    // Toggle badge: tampilkan "Aktif" / "Tidak Aktif" di samping setiap switch
    function updateToggleBadge(checkbox) {
        const badge = document.querySelector('.toggle-badge[data-for="' + checkbox.id + '"]');
        if (!badge) return;
        if (checkbox.checked) {
            badge.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>';
        } else {
            badge.innerHTML = '<span class="badge bg-secondary"><i class="bi bi-dash-circle me-1"></i>Tidak Aktif</span>';
        }
    }
    document.querySelectorAll('.toggle-setting').forEach(function(cb) {
        updateToggleBadge(cb);
        cb.addEventListener('change', function() { updateToggleBadge(this); });
    });

    // Live character counter + status badge untuk semua textarea template
    function updateTemplateStatus(textarea) {
        const counter = document.querySelector('.char-counter[data-target="' + textarea.id + '"]');
        if (!counter) return;
        const len = textarea.value.trim().length;
        // Update char counter (kanan)
        counter.textContent = len > 0 ? textarea.value.length + ' karakter' : '';
        // Update status badge (kiri — sibling pertama dalam div yang sama)
        const statusEl = counter.parentElement.querySelector('small:first-child');
        if (statusEl) {
            if (len > 0) {
                statusEl.className = 'text-success';
                statusEl.innerHTML = '<i class="bi bi-check-circle me-1"></i>Aktif &mdash; ' + textarea.value.length + ' karakter';
            } else {
                statusEl.className = 'text-danger';
                statusEl.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>Kosong &mdash; template belum diisi';
            }
        }
    }
    document.querySelectorAll('.template-textarea').forEach(function(ta) {
        updateTemplateStatus(ta);
        ta.addEventListener('input', function() { updateTemplateStatus(this); });
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

    // ── localStorage persistence ──────────────────────────────────────────
    // Menyimpan dan memulihkan nilai form di browser agar form tidak pernah kosong
    // meski server-side DB/file/cache gagal dibaca.
    const SMS_GW_KEY = 'sms_gw_v1';
    const TEXT_FIELDS   = ['fonnte_api_token','fonnte_device_id','sms_default_country_code',
                           'sms_template_submit','sms_template_status_change','sms_template_published',
                           'wa_template_credential_new','wa_template_credential_update'];
    const TOGGLE_FIELDS = ['sms_gateway_enabled','sms_notification_submit',
                           'sms_notification_status_change','sms_notification_published'];

    function gwSaveToStorage() {
        try {
            const d = {};
            TEXT_FIELDS.forEach(id => {
                const el = document.getElementById(id);
                if (el) d[id] = el.value;
            });
            TOGGLE_FIELDS.forEach(id => {
                const el = document.getElementById(id);
                if (el) d[id] = el.checked ? '1' : '0';
            });
            localStorage.setItem(SMS_GW_KEY, JSON.stringify(d));
        } catch(e) {}
    }

    function gwLoadFromStorage() {
        try {
            const saved = JSON.parse(localStorage.getItem(SMS_GW_KEY) || '{}');
            TEXT_FIELDS.forEach(id => {
                const el = document.getElementById(id);
                if (el && !el.value && saved[id]) {
                    el.value = saved[id];
                    el.dispatchEvent(new Event('input'));
                }
            });
            TOGGLE_FIELDS.forEach(id => {
                const el = document.getElementById(id);
                if (el && !el.checked && saved[id] === '1') {
                    el.checked = true;
                    el.dispatchEvent(new Event('change'));
                }
            });
        } catch(e) {}
    }

    // Simpan ke localStorage saat form di-submit
    const smsForm = document.getElementById('smsGatewayForm');
    if (smsForm) smsForm.addEventListener('submit', gwSaveToStorage);

    // Jika halaman baru saja berhasil disimpan (server render langsung), perbarui localStorage
    @if(session('success'))
    gwSaveToStorage();
    @endif

    // Pulihkan nilai dari localStorage untuk field yang kosong (DB/file/cache gagal baca)
    gwLoadFromStorage();
    updateTokenStatus();
    // ── end localStorage ──────────────────────────────────────────────────

    // ── Banner status berdasarkan nilai field (localStorage + server) ─────
    function updateConfigBanner() {
        try {
            const token  = (document.getElementById('fonnte_api_token')?.value  || '').trim();
            const device = (document.getElementById('fonnte_device_id')?.value  || '').trim();
            const gwOn   = document.getElementById('sms_gateway_enabled')?.checked;
            const warn   = document.getElementById('bannerUnconfigured');
            const ok     = document.getElementById('bannerConfigured');
            const badges = document.getElementById('bannerBadges');
            if (!warn || !ok) return;
            if (token || device) {
                warn.classList.add('d-none');
                ok.classList.remove('d-none');
                ok.style.display = 'flex';
                if (badges) {
                    let html = '';
                    if (token) html += '<div class="col-auto"><span class="badge bg-success"><i class="bi bi-key me-1"></i>Token: ' + token.substring(0,6) + '...' + token.slice(-4) + '</span></div>';
                    if (device) html += '<div class="col-auto"><span class="badge bg-primary"><i class="bi bi-phone me-1"></i>Device: ' + device + '</span></div>';
                    html += '<div class="col-auto"><span class="badge ' + (gwOn ? 'bg-success' : 'bg-secondary') + '"><i class="bi bi-power me-1"></i>Gateway: ' + (gwOn ? 'Aktif' : 'Nonaktif') + '</span></div>';
                    badges.innerHTML = html;
                }
            } else {
                ok.classList.add('d-none');
                warn.classList.remove('d-none');
                warn.style.display = 'flex';
            }
        } catch(e) {}
    }
    updateConfigBanner();
    // ── end banner ────────────────────────────────────────────────────────

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
