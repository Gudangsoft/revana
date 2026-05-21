@extends('layouts.app')

@section('title', 'Detail Tenant — ' . $tenant->name)
@section('page-title', 'Detail Tenant')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    {{-- Hero --}}
    <div class="rounded-3 mb-4 p-4 text-white shadow-sm"
         style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="mb-1 fw-bold">{{ $tenant->name }}</h4>
                @if($tenant->institution)
                <div class="opacity-75 small mb-1"><i class="bi bi-building me-1"></i>{{ $tenant->institution }}</div>
                @endif
                <a href="{{ $tenant->url }}" target="_blank" class="text-white opacity-75 small">
                    <i class="bi bi-globe me-1"></i>{{ $tenant->url }}
                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                </a>
            </div>
            <div class="d-flex flex-wrap gap-3 text-center">
                <div class="rounded-3 px-4 py-2" style="background:rgba(255,255,255,0.18);">
                    <div class="small opacity-75">Status</div>
                    <div>{!! $tenant->status_badge !!}</div>
                </div>
                <div class="rounded-3 px-4 py-2" style="background:rgba(255,255,255,0.18);">
                    <div class="fs-5 fw-bold">{{ $tenant->daysLeft() ?? '∞' }}</div>
                    <div class="small opacity-75">Sisa Hari</div>
                </div>
                <div class="rounded-3 px-4 py-2" style="background:rgba(255,255,255,0.18);">
                    <div class="fs-5 fw-bold">{{ isset($stats['articles']) ? $stats['articles'] : '—' }}</div>
                    <div class="small opacity-75">Artikel</div>
                </div>
                <div class="rounded-3 px-4 py-2" style="background:rgba(255,255,255,0.18);">
                    <div class="fs-5 fw-bold">{{ isset($stats['users']) ? $stats['users'] : '—' }}</div>
                    <div class="small opacity-75">User</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Kolom Kiri --}}
        <div class="col-lg-8">

            {{-- Toggle Fitur --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-toggles me-2 text-primary"></i>Kelola Fitur</span>
                    <span class="text-muted small">Klik toggle untuk aktifkan/nonaktifkan</span>
                </div>
                <div class="card-body">
                    @php
                        $featureGroups = collect($features)->groupBy('group');
                    @endphp
                    @foreach($featureGroups as $group => $groupFeatures)
                    <div class="mb-3">
                        <div class="small fw-semibold text-muted text-uppercase mb-2"
                             style="letter-spacing:0.05em;">{{ $group }}</div>
                        <div class="row g-2">
                            @foreach($groupFeatures as $key => $feat)
                            @php $enabled = $tenant->hasFeature($key); @endphp
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between p-2 rounded border
                                            {{ $enabled ? 'border-success bg-success bg-opacity-10' : 'border-light bg-light' }}">
                                    <div>
                                        <div class="small fw-semibold">{{ $feat['label'] }}</div>
                                        <div class="small text-muted" style="font-size:0.72rem;">{{ $key }}</div>
                                    </div>
                                    <form action="{{ route('admin.tenants.toggle-feature', [$tenant, $key]) }}"
                                          method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm {{ $enabled ? 'btn-success' : 'btn-outline-secondary' }}"
                                                title="{{ $enabled ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="bi {{ $enabled ? 'bi-toggle-on' : 'bi-toggle-off' }} fs-5"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Statistik DB --}}
            @if(!isset($stats['error']))
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header fw-semibold">
                    <i class="bi bi-database me-2 text-info"></i>Statistik Database Tenant
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach(['users'=>'User Admin','pics'=>'PIC','journals'=>'Jurnal','articles'=>'Artikel'] as $key => $label)
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-light text-center p-3">
                                <div class="fs-4 fw-bold text-primary">{{ $stats[$key] ?? 0 }}</div>
                                <div class="small text-muted">{{ $label }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Tidak bisa terhubung ke DB tenant: {{ $stats['error'] }}
            </div>
            @endif

        </div>

        {{-- Kolom Kanan --}}
        <div class="col-lg-4">

            {{-- Info Tenant --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header fw-semibold">
                    <i class="bi bi-info-circle me-2"></i>Informasi Tenant
                </div>
                <div class="card-body small">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">Subdomain</td><td class="fw-semibold">{{ $tenant->subdomain }}.apji.org</td></tr>
                        <tr><td class="text-muted">Database</td><td class="fw-semibold font-monospace" style="font-size:0.8rem;">{{ $tenant->db_name }}</td></tr>
                        <tr><td class="text-muted">Paket</td><td><span class="badge bg-primary">{{ ucfirst($tenant->plan) }}</span></td></tr>
                        <tr><td class="text-muted">Email</td><td>{{ $tenant->email ?: '—' }}</td></tr>
                        <tr><td class="text-muted">Telepon</td><td>{{ $tenant->phone ?: '—' }}</td></tr>
                        <tr><td class="text-muted">Admin</td><td>{{ $tenant->admin_name ?: '—' }}</td></tr>
                        <tr><td class="text-muted">Email Admin</td><td>{{ $tenant->admin_email ?: '—' }}</td></tr>
                        <tr><td class="text-muted">Trial s.d.</td><td>{{ $tenant->trial_ends_at?->format('d/m/Y') ?: '—' }}</td></tr>
                        <tr><td class="text-muted">Expires</td><td>{{ $tenant->expires_at?->format('d/m/Y') ?: '—' }}</td></tr>
                        <tr><td class="text-muted">Dibuat</td><td>{{ $tenant->created_at->format('d/m/Y H:i') }}</td></tr>
                    </table>
                    @if($tenant->notes)
                    <div class="mt-2 p-2 bg-light rounded border-start border-2 border-warning text-muted">
                        <i class="bi bi-sticky me-1"></i>{{ $tenant->notes }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Impersonate --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header fw-semibold">
                    <i class="bi bi-person-badge me-2 text-purple"></i>Impersonate
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Login sementara sebagai admin tenant ini tanpa mengetahui password.</p>
                    <form action="{{ route('admin.tenants.impersonate', $tenant) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-purple w-100 btn-sm"
                                style="border-color:#7c3aed;color:#7c3aed;"
                                onmouseover="this.style.background='#7c3aed';this.style.color='white'"
                                onmouseout="this.style.background='';this.style.color='#7c3aed'">
                            <i class="bi bi-person-badge-fill me-1"></i>Masuk sebagai Admin Tenant
                        </button>
                    </form>
                </div>
            </div>

            {{-- Perpanjang / Ubah Paket --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header fw-semibold">
                    <i class="bi bi-calendar-plus me-2 text-success"></i>Perpanjang / Ubah Paket
                </div>
                <div class="card-body">
                    @if($tenant->plan === 'lifetime')
                    <div class="alert alert-success py-2 mb-3 text-center small">
                        <i class="bi bi-infinity me-1"></i>Paket <strong>Lifetime</strong> — tidak perlu perpanjang.
                    </div>
                    @else
                    <form action="{{ route('admin.tenants.renew', $tenant) }}" method="POST" class="mb-3">
                        @csrf
                        <label class="form-label small fw-semibold">Perpanjang (hari)</label>
                        <div class="input-group input-group-sm">
                            <select name="days" class="form-select">
                                <option value="30">30 hari</option>
                                <option value="60">60 hari</option>
                                <option value="90">90 hari</option>
                                <option value="180">180 hari</option>
                                <option value="365" selected>365 hari (1 tahun)</option>
                            </select>
                            <button type="submit" class="btn btn-success btn-sm">Perpanjang</button>
                        </div>
                    </form>
                    @endif
                    <form action="{{ route('admin.tenants.change-plan', $tenant) }}" method="POST">
                        @csrf
                        <label class="form-label small fw-semibold">Ubah Paket</label>
                        <div class="input-group input-group-sm">
                            <select name="plan" class="form-select">
                                @foreach($plans as $key => $plan)
                                <option value="{{ $key }}" {{ $tenant->plan === $key ? 'selected' : '' }}>
                                    {{ $plan['label'] }} {{ $plan['duration'] ? '('.$plan['duration'].' hari)' : '(Seumur Hidup)' }}
                                </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm"
                                    onclick="return confirm('Mengubah paket akan mereset fitur sesuai paket baru. Lanjutkan?')">
                                Ubah
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Aksi --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header fw-semibold">
                    <i class="bi bi-lightning me-2"></i>Aksi
                </div>
                <div class="card-body d-grid gap-2">

                    {{-- Setup ulang DB + Migrasi --}}
                    @if(isset($stats['db_ok']) && !$stats['db_ok'])
                    <form action="{{ route('admin.tenants.setup-db', $tenant) }}" method="POST"
                          onsubmit="return confirm('Buat database + jalankan migrasi untuk {{ $tenant->name }}?')">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 btn-sm">
                            <i class="bi bi-database-add me-1"></i>Buat Database &amp; Migrasi
                        </button>
                    </form>
                    @endif

                    {{-- Migrate --}}
                    <form action="{{ route('admin.tenants.migrate', $tenant) }}" method="POST"
                          onsubmit="return confirm('Jalankan migration ke tenant ini?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-info w-100 btn-sm">
                            <i class="bi bi-arrow-repeat me-1"></i>Jalankan Migration
                        </button>
                    </form>

                    {{-- Suspend / Aktifkan --}}
                    @if($tenant->status === 'suspended')
                    <form action="{{ route('admin.tenants.activate', $tenant) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 btn-sm">
                            <i class="bi bi-play-circle me-1"></i>Aktifkan Tenant
                        </button>
                    </form>
                    @else
                    <form action="{{ route('admin.tenants.suspend', $tenant) }}" method="POST"
                          onsubmit="return confirm('Suspend tenant {{ $tenant->name }}?')">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100 btn-sm">
                            <i class="bi bi-pause-circle me-1"></i>Suspend Tenant
                        </button>
                    </form>
                    @endif

                    <hr class="my-1">

                    {{-- Hapus tenant --}}
                    <button type="button" class="btn btn-outline-danger w-100 btn-sm"
                            data-bs-toggle="modal" data-bs-target="#modalHapus">
                        <i class="bi bi-trash me-1"></i>Hapus Tenant & Database
                    </button>
                </div>
            </div>

            {{-- Branding --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header fw-semibold">
                    <i class="bi bi-palette me-2 text-info"></i>Branding Tenant
                </div>
                <div class="card-body">
                    @php $b = $tenant->branding ?? []; @endphp
                    <form action="{{ route('admin.tenants.branding', $tenant) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Nama Aplikasi</label>
                            <input type="text" name="app_name" class="form-control form-control-sm"
                                   placeholder="Contoh: SIPERA IAIN Mataram"
                                   value="{{ $b['app_name'] ?? '' }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Tagline</label>
                            <input type="text" name="tagline" class="form-control form-control-sm"
                                   placeholder="Sistem Insentif Reviewer ..."
                                   value="{{ $b['tagline'] ?? '' }}">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">URL Logo</label>
                            <input type="url" name="logo_url" class="form-control form-control-sm"
                                   placeholder="https://..."
                                   value="{{ $b['logo_url'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold mb-1">Warna Utama</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="color" name="primary_color" class="form-control form-control-color form-control-sm"
                                       value="{{ $b['primary_color'] ?? '#4f46e5' }}" style="width:3rem;">
                                <input type="text" id="colorHex" class="form-control form-control-sm font-monospace"
                                       value="{{ $b['primary_color'] ?? '#4f46e5' }}" maxlength="7"
                                       oninput="document.querySelector('[name=primary_color]').value=this.value">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-info btn-sm text-white w-100">
                            <i class="bi bi-save me-1"></i>Simpan Branding
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Modal Hapus --}}
<div class="modal fade" id="modalHapus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Hapus Tenant</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong>PERINGATAN!</strong> Tindakan ini akan menghapus:
                    <ul class="mb-0 mt-1">
                        <li>Database <strong>{{ $tenant->db_name }}</strong> beserta seluruh datanya</li>
                        <li>Record tenant <strong>{{ $tenant->name }}</strong></li>
                    </ul>
                    <strong>Data tidak dapat dipulihkan!</strong>
                </div>
                <p>Ketik nama tenant untuk konfirmasi:</p>
                <form action="{{ route('admin.tenants.destroy', $tenant) }}" method="POST">
                    @csrf @method('DELETE')
                    <input type="hidden" name="_tenant_name" value="{{ $tenant->name }}">
                    <input type="text" name="confirm_name" class="form-control mb-3"
                           placeholder="{{ $tenant->name }}" autocomplete="off">
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-trash me-1"></i>Hapus Permanen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.querySelector('[name=primary_color]')?.addEventListener('input', function() {
    const hex = document.getElementById('colorHex');
    if (hex) hex.value = this.value;
});
</script>
@endpush
@endsection
