@extends('layouts.app')

@section('title', 'Tambah Tenant Baru')
@section('page-title', 'Tambah Tenant Baru')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <a href="{{ route('admin.tenants.tutorial') }}" class="btn btn-outline-info btn-sm" target="_blank">
                <i class="bi bi-book me-1"></i>Baca Panduan Lengkap
            </a>
        </div>

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Info Database & Setup MySQL Admin --}}
        @php
            $dbAdminUser = env('DB_ADMIN_USERNAME', env('DB_USERNAME', '?'));
            $dbAdminConfigured = env('DB_ADMIN_USERNAME') !== null;
            $dbUser = env('DB_USERNAME', '?');
            $dbHost = env('DB_HOST', '127.0.0.1');
        @endphp
        <div class="card mb-3 border-info">
            <div class="card-header bg-info text-white py-2">
                <i class="bi bi-database-gear me-2"></i><strong>Info Database Tenant</strong>
            </div>
            <div class="card-body pb-2">
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Nama Database yang akan dibuat</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><code>tenant_</code></span>
                            <input type="text" class="form-control form-control-sm font-monospace" id="dbPreview"
                                   value="(isi subdomain dulu)" readonly>
                        </div>
                        <small class="text-muted">Format: <code>tenant_{subdomain}</code></small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">MySQL Admin User</label>
                        <input type="text" class="form-control form-control-sm font-monospace"
                               value="{{ $dbAdminUser }}" readonly>
                        <small class="{{ $dbAdminConfigured ? 'text-success' : 'text-danger' }}">
                            @if($dbAdminConfigured)
                                <i class="bi bi-check-circle"></i> DB_ADMIN_USERNAME dikonfigurasi
                            @else
                                <i class="bi bi-exclamation-triangle"></i> Belum ada DB_ADMIN_USERNAME di .env
                            @endif
                        </small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">App DB User</label>
                        <input type="text" class="form-control form-control-sm font-monospace"
                               value="{{ $dbUser }}" readonly>
                        <small class="text-muted">User untuk akses data tenant</small>
                    </div>
                </div>

                @if(!$dbAdminConfigured)
                <div class="alert alert-warning py-2 mb-2">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Perlu setup:</strong> Tambahkan ke file <code>.env</code> di server:
                    <pre class="bg-dark text-light rounded p-2 mt-2 mb-1 small">DB_ADMIN_USERNAME=root
DB_ADMIN_PASSWORD=password_root_mysql</pre>
                    Atau jalankan perintah ini di MySQL untuk memberi privilege ke user aplikasi:
                    <pre class="bg-dark text-light rounded p-2 mt-1 mb-0 small">GRANT CREATE, DROP ON *.* TO '{{ $dbUser }}'@'{{ $dbHost }}';
FLUSH PRIVILEGES;</pre>
                </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-building-add me-2"></i><strong>Form Pendaftaran Tenant Baru</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.tenants.store') }}" method="POST">
                    @csrf

                    <h6 class="fw-semibold text-primary mb-3 border-bottom pb-2">
                        <i class="bi bi-info-circle me-2"></i>Informasi Institusi
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Klien / Kontak <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Nama PIC atau kontak utama" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Institusi</label>
                            <input type="text" name="institution" class="form-control @error('institution') is-invalid @enderror"
                                   value="{{ old('institution') }}" placeholder="Universitas / Sekolah Tinggi / dll.">
                            @error('institution')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="kontak@institusi.ac.id">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. WhatsApp</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}" placeholder="628123456789">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="fw-semibold text-primary mb-3 border-bottom pb-2">
                        <i class="bi bi-globe me-2"></i>Domain & Paket
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subdomain <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="subdomain" id="subdomainInput"
                                       class="form-control @error('subdomain') is-invalid @enderror"
                                       value="{{ old('subdomain') }}"
                                       placeholder="univ-a"
                                       pattern="[a-z0-9\-]+"
                                       oninput="updatePreview()" required>
                                <span class="input-group-text">.apji.org</span>
                                @error('subdomain')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text">
                                Preview: <strong id="domainPreview" class="text-primary">—.apji.org</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Paket <span class="text-danger">*</span></label>
                            <select name="plan" id="planSelect" class="form-select @error('plan') is-invalid @enderror"
                                    onchange="updateFeaturePreview()" required>
                                @foreach($plans as $key => $plan)
                                <option value="{{ $key }}" {{ old('plan', 'trial') === $key ? 'selected' : '' }}>
                                    {{ $plan['label'] }} ({{ $plan['duration'] ? $plan['duration'] . ' hari' : 'Seumur Hidup' }})
                                </option>
                                @endforeach
                            </select>
                            @error('plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Preview fitur per plan --}}
                    <div class="alert alert-info py-2 px-3 mb-4" id="featurePreview">
                        <div class="small fw-semibold mb-1"><i class="bi bi-toggles me-1"></i>Fitur yang akan diaktifkan:</div>
                        <div id="featureList" class="d-flex flex-wrap gap-1"></div>
                    </div>

                    <h6 class="fw-semibold text-primary mb-3 border-bottom pb-2">
                        <i class="bi bi-person-gear me-2"></i>Admin Default Tenant <small class="text-muted fw-normal">(opsional)</small>
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Admin</label>
                            <input type="text" name="admin_name" class="form-control @error('admin_name') is-invalid @enderror"
                                   value="{{ old('admin_name') }}" placeholder="Nama admin tenant">
                            @error('admin_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Admin</label>
                            <input type="email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror"
                                   value="{{ old('admin_email') }}" placeholder="admin@institusi.ac.id">
                            @error('admin_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Catatan Internal</label>
                        <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
                                  placeholder="Catatan internal tentang klien ini...">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-building-add me-1"></i>Buat Tenant
                        </button>
                        <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
const plans = @json($plans);
const features = @json($features);

function updatePreview() {
    const val = document.getElementById('subdomainInput').value.toLowerCase().replace(/[^a-z0-9\-]/g, '');
    document.getElementById('domainPreview').textContent = (val || '—') + '.apji.org';
    // Update DB name preview
    const dbPreview = document.getElementById('dbPreview');
    if (dbPreview) {
        dbPreview.value = val ? val.replace(/-/g, '_') : '(isi subdomain dulu)';
    }
}
document.getElementById('subdomainInput')?.addEventListener('input', updatePreview);

function updateFeaturePreview() {
    const plan = document.getElementById('planSelect').value;
    const planFeatures = plans[plan]?.features || [];
    const list = document.getElementById('featureList');
    list.innerHTML = '';
    Object.entries(features).forEach(([key, feat]) => {
        const active = planFeatures.includes(key);
        const badge = document.createElement('span');
        badge.className = 'badge ' + (active ? 'bg-success' : 'bg-light text-muted border');
        badge.innerHTML = (active ? '✓ ' : '✗ ') + feat.label;
        list.appendChild(badge);
    });
}

updateFeaturePreview();
updatePreview();
</script>
@endsection
