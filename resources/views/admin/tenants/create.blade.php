@extends('layouts.app')

@section('title', 'Tambah Tenant Baru')
@section('page-title', 'Tambah Tenant Baru')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="mb-3">
            <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

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
                                    {{ $plan['label'] }} ({{ $plan['duration'] }} hari)
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
    const val = document.getElementById('subdomainInput').value.toLowerCase();
    document.getElementById('domainPreview').textContent = (val || '—') + '.apji.org';
}

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
