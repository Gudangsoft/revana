@extends('layouts.app')

@section('title', 'Manajemen Tenant')
@section('page-title', 'Manajemen Tenant')

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

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0 fw-bold"><i class="bi bi-building me-2 text-primary"></i>Daftar Tenant / Klien</h5>
            <div class="small text-muted">Kelola semua institusi yang menggunakan sistem SIPERA</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tenants.tutorial') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-book me-1"></i>Panduan
            </a>
            <form action="{{ route('admin.tenants.migrate-all') }}" method="POST"
                  onsubmit="return confirm('Jalankan migration ke semua tenant?')">
                @csrf
                <button type="submit" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-arrow-repeat me-1"></i>Migrate Semua
                </button>
            </form>
            <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Tambah Tenant
            </a>
        </div>
    </div>

    {{-- Summary cards --}}
    @php
        $total     = $tenants->count();
        $active    = $tenants->whereIn('status', ['active', 'trial'])->count();
        $suspended = $tenants->where('status', 'suspended')->count();
        $expired   = $tenants->where('status', 'expired')->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-building fs-2 opacity-75"></i>
                    <div><div class="fs-4 fw-bold">{{ $total }}</div><div class="small opacity-75">Total Tenant</div></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success text-white shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-check-circle fs-2 opacity-75"></i>
                    <div><div class="fs-4 fw-bold">{{ $active }}</div><div class="small opacity-75">Aktif / Trial</div></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-danger text-white shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-pause-circle fs-2 opacity-75"></i>
                    <div><div class="fs-4 fw-bold">{{ $suspended }}</div><div class="small opacity-75">Suspended</div></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-secondary text-white shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-clock-history fs-2 opacity-75"></i>
                    <div><div class="fs-4 fw-bold">{{ $expired }}</div><div class="small opacity-75">Expired</div></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel tenant --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-table me-2"></i><strong>Semua Tenant</strong></span>
            <span class="badge bg-secondary">{{ $total }} klien</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama / Institusi</th>
                        <th>Subdomain</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Fitur Aktif</th>
                        <th>Sisa Hari</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $tenant->name }}</div>
                            @if($tenant->institution)
                            <div class="small text-muted">{{ $tenant->institution }}</div>
                            @endif
                            @if($tenant->email)
                            <div class="small text-muted"><i class="bi bi-envelope me-1"></i>{{ $tenant->email }}</div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ $tenant->url }}" target="_blank" class="text-decoration-none">
                                <span class="badge bg-light text-dark border">
                                    {{ $tenant->subdomain }}.apji.org
                                    <i class="bi bi-box-arrow-up-right ms-1" style="font-size:0.65rem;"></i>
                                </span>
                            </a>
                            <div class="small text-muted mt-1">DB: {{ $tenant->db_name }}</div>
                        </td>
                        <td>
                            @php
                                $planColors = ['trial'=>'info','basic'=>'primary','pro'=>'success','enterprise'=>'warning text-dark'];
                                $planColor  = $planColors[$tenant->plan] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $planColor }}">{{ ucfirst($tenant->plan) }}</span>
                        </td>
                        <td>{!! $tenant->status_badge !!}</td>
                        <td>
                            @php
                                $activeFeatures = collect($features)->filter(fn($f, $key) => $tenant->hasFeature($key));
                            @endphp
                            <span class="badge bg-primary rounded-pill">{{ $activeFeatures->count() }}/{{ count($features) }}</span>
                        </td>
                        <td>
                            @php $days = $tenant->daysLeft(); @endphp
                            @if($days !== null)
                                <span class="badge {{ $days <= 7 ? 'bg-danger' : ($days <= 30 ? 'bg-warning text-dark' : 'bg-success') }}">
                                    {{ $days }} hari
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.tenants.show', $tenant) }}"
                               class="btn btn-outline-primary btn-sm" title="Detail & Kelola">
                                <i class="bi bi-gear"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-building fs-3 d-block mb-2"></i>
                            Belum ada tenant. <a href="{{ route('admin.tenants.create') }}">Tambah sekarang</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
