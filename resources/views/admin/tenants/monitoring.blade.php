@extends('layouts.app')

@section('title', 'Monitoring Tenant')
@section('page-title', 'Monitoring Tenant')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">

    <div class="mb-3 d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
        </a>
        <span class="text-muted small">
            <i class="bi bi-clock me-1"></i>Data diambil saat halaman dimuat — {{ now()->format('H:i:s') }}
        </span>
    </div>

    {{-- Ringkasan --}}
    @php
        $total     = $tenants->count();
        $active    = $tenants->whereIn('status', ['active', 'trial'])->count();
        $suspended = $tenants->where('status', 'suspended')->count();
        $expired   = $tenants->where('status', 'expired')->count();
        $dbOk      = collect($statsList)->where('db_ok', true)->count();
        $dbFail    = collect($statsList)->where('db_ok', false)->count();
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-3 fw-bold text-primary">{{ $total }}</div>
                <div class="small text-muted">Total</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-3 fw-bold text-success">{{ $active }}</div>
                <div class="small text-muted">Aktif</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-3 fw-bold text-warning">{{ $suspended }}</div>
                <div class="small text-muted">Suspended</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-3 fw-bold text-secondary">{{ $expired }}</div>
                <div class="small text-muted">Expired</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-3 fw-bold text-info">{{ $dbOk }}</div>
                <div class="small text-muted">DB OK</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-3 fw-bold text-danger">{{ $dbFail }}</div>
                <div class="small text-muted">DB Error</div>
            </div>
        </div>
    </div>

    {{-- Tabel Detail --}}
    <div class="card shadow-sm border-0">
        <div class="card-header fw-semibold">
            <i class="bi bi-activity me-2 text-primary"></i>Status Semua Tenant
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tenant</th>
                            <th>Paket</th>
                            <th>Status</th>
                            <th class="text-center">Sisa Hari</th>
                            <th class="text-center">User</th>
                            <th class="text-center">PIC</th>
                            <th class="text-center">Artikel</th>
                            <th class="text-center">Login Terakhir</th>
                            <th class="text-center">DB</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                        @php
                            $s = $statsList[$tenant->id] ?? [];
                            $dbOkTenant = $s['db_ok'] ?? false;
                            $daysLeft = $tenant->daysLeft();
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $tenant->name }}</div>
                                <div class="text-muted" style="font-size:0.75rem;">
                                    <a href="{{ $tenant->url }}" target="_blank" class="text-decoration-none">
                                        {{ $tenant->subdomain }}.apji.org
                                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ ucfirst($tenant->plan) }}</span>
                            </td>
                            <td>{!! $tenant->status_badge !!}</td>
                            <td class="text-center">
                                @if($daysLeft === null)
                                    @if($tenant->plan === 'lifetime')
                                        <span class="badge bg-success">∞</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                @elseif($daysLeft <= 3)
                                    <span class="badge bg-danger">{{ $daysLeft }}h</span>
                                @elseif($daysLeft <= 7)
                                    <span class="badge bg-warning text-dark">{{ $daysLeft }}h</span>
                                @else
                                    <span class="badge bg-light text-dark border">{{ $daysLeft }}h</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $dbOkTenant ? ($s['users'] ?? 0) : '—' }}
                            </td>
                            <td class="text-center">
                                {{ $dbOkTenant ? ($s['pics'] ?? 0) : '—' }}
                            </td>
                            <td class="text-center">
                                {{ $dbOkTenant ? ($s['articles'] ?? 0) : '—' }}
                            </td>
                            <td class="text-center" style="font-size:0.75rem;">
                                @if($dbOkTenant && !empty($s['last_login']))
                                    {{ \Carbon\Carbon::parse($s['last_login'])->diffForHumans() }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($dbOkTenant)
                                    <i class="bi bi-check-circle-fill text-success" title="DB OK"></i>
                                @else
                                    <i class="bi bi-x-circle-fill text-danger"
                                       title="{{ $s['error'] ?? 'Error' }}"></i>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.tenants.show', $tenant) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                Belum ada tenant terdaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tenant expired peringatan --}}
    @php
        $expiringSoon = $tenants->filter(function($t) {
            $d = $t->daysLeft();
            return $d !== null && $d <= 7 && $t->isActive();
        });
    @endphp
    @if($expiringSoon->isNotEmpty())
    <div class="alert alert-warning mt-4">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Tenant Akan Segera Expired:</strong>
        <ul class="mb-0 mt-1">
            @foreach($expiringSoon as $t)
            <li>
                <strong>{{ $t->name }}</strong> — sisa
                <span class="fw-bold text-danger">{{ $t->daysLeft() }} hari</span>
                <a href="{{ route('admin.tenants.show', $t) }}" class="ms-2 small">Perpanjang</a>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

</div>
@endsection
