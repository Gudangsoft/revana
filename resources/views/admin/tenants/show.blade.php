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

            {{-- Aksi --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header fw-semibold">
                    <i class="bi bi-lightning me-2"></i>Aksi
                </div>
                <div class="card-body d-grid gap-2">

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
@endsection
