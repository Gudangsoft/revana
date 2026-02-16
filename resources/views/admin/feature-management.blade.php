@extends('layouts.app')

@section('title', 'Feature Management - Admin')
@section('page-title', 'Feature Management')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-toggles"></i> Feature Management</h4>
        <p class="text-muted mb-0">Kelola fitur, batasan, role, dan sistem dari satu halaman.</p>
    </div>
    <div>
        <form action="{{ route('admin.feature-management.reset') }}" method="POST" class="d-inline"
              onsubmit="return confirm('Reset semua pengaturan fitur ke default?')">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-counterclockwise"></i> Reset Default
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- QUICK NAV TABS -->
<ul class="nav nav-pills mb-4 flex-wrap gap-1" id="featureTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="toggle-tab" data-bs-toggle="pill" data-bs-target="#togglePanel" type="button" role="tab">
            <i class="bi bi-toggles"></i> Feature Toggles
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="limits-tab" data-bs-toggle="pill" data-bs-target="#limitsPanel" type="button" role="tab">
            <i class="bi bi-sliders"></i> Configurable Limits
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="roles-tab" data-bs-toggle="pill" data-bs-target="#rolesPanel" type="button" role="tab">
            <i class="bi bi-people-fill"></i> Role System
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="maintenance-tab" data-bs-toggle="pill" data-bs-target="#maintenancePanel" type="button" role="tab">
            <i class="bi bi-cone-striped"></i> Maintenance
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="system-tab" data-bs-toggle="pill" data-bs-target="#systemPanel" type="button" role="tab">
            <i class="bi bi-cpu"></i> System Info
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="changelog-tab" data-bs-toggle="pill" data-bs-target="#changelogPanel" type="button" role="tab">
            <i class="bi bi-journal-code"></i> Changelog
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="audit-tab" data-bs-toggle="pill" data-bs-target="#auditPanel" type="button" role="tab">
            <i class="bi bi-clock-history"></i> Audit Log
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="importexport-tab" data-bs-toggle="pill" data-bs-target="#importexportPanel" type="button" role="tab">
            <i class="bi bi-arrow-left-right"></i> Import/Export
        </button>
    </li>
</ul>

<form action="{{ route('admin.feature-management.save') }}" method="POST">
@csrf

<div class="tab-content" id="featureTabContent">
<!-- ============================== -->
<!-- TAB 1: FEATURE TOGGLES         -->
<!-- ============================== -->
<div class="tab-pane fade show active" id="togglePanel" role="tabpanel">
    <div class="row g-3">
        @foreach($groupedFeatures as $groupName => $features)
        <div class="col-12">
            <h6 class="text-muted text-uppercase fw-bold mb-2">
                <i class="bi bi-folder2 me-1"></i> {{ $groupName }}
            </h6>
        </div>
        @foreach($features as $key => $meta)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm {{ $featureSettings[$key] === '1' ? '' : 'opacity-75' }}" id="card-{{ $key }}">
                <div class="card-body d-flex align-items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle bg-{{ $meta['color'] }} bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi {{ $meta['icon'] }} text-{{ $meta['color'] }} fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="small">{{ $meta['label'] }}</strong>
                            @if(isset($envOverrides[$key]))
                                <span class="badge bg-dark" title="Diatur oleh .env, tidak bisa diubah dari UI"><i class="bi bi-lock-fill me-1"></i>ENV</span>
                            @else
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       name="{{ $key }}" id="switch-{{ $key }}" value="1"
                                       {{ $featureSettings[$key] === '1' ? 'checked' : '' }}
                                       onchange="toggleCardOpacity('{{ $key }}', this.checked)">
                            </div>
                            @endif
                        </div>
                        <small class="text-muted">{{ $meta['desc'] }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        @endforeach
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Simpan Pengaturan Fitur
        </button>
    </div>
</div>

<!-- ============================== -->
<!-- TAB 2: CONFIGURABLE LIMITS     -->
<!-- ============================== -->
<div class="tab-pane fade" id="limitsPanel" role="tabpanel">
    <div class="row g-3">
        @foreach($limitMeta as $key => $meta)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi {{ $meta['icon'] }} text-primary me-2 fs-5"></i>
                        <strong>{{ $meta['label'] }}</strong>
                    </div>
                    <p class="text-muted small mb-3">{{ $meta['desc'] }}</p>
                    <div class="input-group">
                        <input type="number" class="form-control" name="{{ $key }}"
                               value="{{ $featureSettings[$key] ?? '' }}"
                               min="{{ $meta['min'] }}" max="{{ $meta['max'] }}">
                        <span class="input-group-text small">{{ $meta['unit'] }}</span>
                    </div>
                    <div class="form-text small">Range: {{ $meta['min'] }} - {{ $meta['max'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Simpan Pengaturan Limit
        </button>
    </div>
</div>

<!-- ============================== -->
<!-- TAB 3: ROLE SYSTEM              -->
<!-- ============================== -->
<div class="tab-pane fade" id="rolesPanel" role="tabpanel">
    <!-- Role Cards -->
    <div class="row g-3 mb-4">
        @foreach($roleDefinitions as $roleKey => $role)
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-{{ $role['color'] }} text-white py-2">
                    <h6 class="mb-0"><i class="bi {{ $role['icon'] }} me-1"></i> {{ $role['label'] }}</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">{{ $role['desc'] }}</small>
                    @if($role['editable'])
                        <div class="mt-2"><span class="badge bg-success bg-opacity-75"><i class="bi bi-pencil-square me-1"></i>Editable</span></div>
                    @else
                        <div class="mt-2"><span class="badge bg-secondary bg-opacity-50"><i class="bi bi-lock me-1"></i>Fixed</span></div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Editable Capability Matrix -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-grid-3x3-gap me-1"></i> Capability Matrix per Role</h6>
            <small class="text-warning"><i class="bi bi-pencil-square me-1"></i> Marketing &amp; PIC bisa diedit</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="bg-light" style="min-width: 220px;">Capability</th>
                            @foreach($roleDefinitions as $roleKey => $role)
                            <th class="text-center bg-{{ $role['color'] }} bg-opacity-10" style="min-width: {{ $role['editable'] ? '130px' : '100px' }};">
                                <i class="bi {{ $role['icon'] }} text-{{ $role['color'] }}"></i><br>
                                <small class="fw-bold">{{ $role['label'] }}</small>
                                @if($role['editable'])
                                    <br><span class="badge bg-success" style="font-size: 0.55rem;">EDIT</span>
                                @endif
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($capabilityDefs as $capKey => $capMeta)
                        <tr>
                            <td>
                                <i class="bi {{ $capMeta['icon'] }} text-muted me-1"></i>
                                <small class="fw-semibold">{{ $capMeta['label'] }}</small>
                            </td>
                            @foreach($roleDefinitions as $roleKey => $role)
                            @php
                                $val = $role['capabilities'][$capMeta['label']] ?? 'no';
                            @endphp
                            @if($role['editable'])
                                {{-- Editable dropdown for Marketing & PIC --}}
                                <td class="text-center p-1">
                                    <select name="role_{{ $roleKey }}_{{ $capKey }}"
                                            class="form-select form-select-sm text-center capability-select"
                                            data-role="{{ $roleKey }}" data-cap="{{ $capKey }}"
                                            style="font-size: 0.75rem; min-width: 110px;"
                                            onchange="updateCapBg(this)">
                                        @foreach($capabilityOptions as $optVal => $optLabel)
                                        <option value="{{ $optVal }}" {{ $val === $optVal ? 'selected' : '' }}>
                                            {{ $optLabel }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                            @else
                                {{-- Read-only badge for Admin & Reviewer --}}
                                @php
                                    $bgMap = ['yes' => '#e8f5e9', 'no' => '#fce4ec', 'read-only' => '#fff8e1', 'partial' => '#e3f2fd'];
                                    $bgColor = $bgMap[$val] ?? '#f5f5f5';
                                @endphp
                                <td class="text-center" style="background: {{ $bgColor }};">
                                    @if($val === 'yes')
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @elseif($val === 'no')
                                        <i class="bi bi-x-circle text-danger"></i>
                                    @elseif($val === 'read-only')
                                        <i class="bi bi-eye text-warning"></i>
                                    @elseif($val === 'partial')
                                        <i class="bi bi-dash-circle text-info"></i>
                                    @endif
                                </td>
                            @endif
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light">
            <div class="d-flex flex-wrap gap-3 small mb-2">
                <span><i class="bi bi-check-circle-fill text-success"></i> Ya (Full)</span>
                <span><i class="bi bi-x-circle text-danger"></i> Tidak</span>
                <span><i class="bi bi-eye text-warning"></i> Read Only</span>
                <span><i class="bi bi-dash-circle text-info"></i> Sebagian</span>
            </div>

            {{-- Quick action: Samakan PIC = Marketing --}}
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-success btn-sm" onclick="copyCapabilities('marketing', 'pic')">
                    <i class="bi bi-arrow-right me-1"></i> Samakan PIC = Marketing
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="copyCapabilities('pic', 'marketing')">
                    <i class="bi bi-arrow-left me-1"></i> Samakan Marketing = PIC
                </button>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Simpan Pengaturan Role
        </button>
    </div>
</div>

<!-- ============================== -->
<!-- TAB 4: MAINTENANCE MODE         -->
<!-- ============================== -->
<div class="tab-pane fade" id="maintenancePanel" role="tabpanel">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-cone-striped me-1"></i> Maintenance Mode</h5>
                </div>
                <div class="card-body">
                    <div class="alert {{ $featureSettings['maintenance_mode'] === '1' ? 'alert-danger' : 'alert-success' }} d-flex align-items-center">
                        <i class="bi {{ $featureSettings['maintenance_mode'] === '1' ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill' }} me-2 fs-4"></i>
                        <div>
                            <strong>Status: {{ $featureSettings['maintenance_mode'] === '1' ? 'AKTIF (MAINTENANCE)' : 'NORMAL (Sistem Berjalan)' }}</strong>
                            <br><small class="text-muted">Saat maintenance aktif, hanya Admin yang bisa mengakses sistem. Semua user lain akan melihat halaman maintenance.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="maintenance_mode" id="maintenanceSwitch" value="1"
                                   {{ $featureSettings['maintenance_mode'] === '1' ? 'checked' : '' }}
                                   onchange="toggleMaintenanceWarning(this.checked)">
                            <label class="form-check-label fw-bold" for="maintenanceSwitch">
                                Aktifkan Maintenance Mode
                            </label>
                        </div>
                    </div>

                    <div id="maintenanceWarning" class="alert alert-warning {{ $featureSettings['maintenance_mode'] === '1' ? '' : 'd-none' }}">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Perhatian!</strong> Semua Marketing, PIC, dan Reviewer tidak akan bisa mengakses sistem!
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pesan Maintenance</label>
                        <textarea class="form-control" name="maintenance_message" rows="3">{{ $featureSettings['maintenance_message'] }}</textarea>
                        <div class="form-text">Pesan yang ditampilkan ke user saat maintenance aktif.</div>
                    </div>

                    <hr>
                    <h6 class="fw-bold mb-3"><i class="bi bi-calendar-event me-1"></i> Jadwal Maintenance Otomatis</h6>
                    <p class="text-muted small">Set jadwal untuk mengaktifkan dan menonaktifkan maintenance mode secara otomatis.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mulai Maintenance</label>
                            <input type="datetime-local" class="form-control" name="maintenance_scheduled_start"
                                   value="{{ $featureSettings['maintenance_scheduled_start'] ?? '' }}">
                            <div class="form-text">Kosongkan jika tidak ingin menjadwalkan.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Selesai Maintenance</label>
                            <input type="datetime-local" class="form-control" name="maintenance_scheduled_end"
                                   value="{{ $featureSettings['maintenance_scheduled_end'] ?? '' }}">
                            <div class="form-text">Sistem akan otomatis kembali normal.</div>
                        </div>
                    </div>
                    @if(!empty($featureSettings['maintenance_scheduled_start']) || !empty($featureSettings['maintenance_scheduled_end']))
                    <div class="alert alert-info mt-3 small">
                        <i class="bi bi-info-circle me-1"></i>
                        @if(!empty($featureSettings['maintenance_scheduled_start']))
                            Dijadwalkan mulai: <strong>{{ $featureSettings['maintenance_scheduled_start'] }}</strong>
                        @endif
                        @if(!empty($featureSettings['maintenance_scheduled_end']))
                            | Selesai: <strong>{{ $featureSettings['maintenance_scheduled_end'] }}</strong>
                        @endif
                    </div>
                    @endif

                    @if(isset($envOverrides['maintenance_mode']))
                    <div class="alert alert-dark mt-3">
                        <i class="bi bi-lock-fill me-1"></i> Maintenance mode dikontrol oleh <code>.env</code> (FORCE_MAINTENANCE). Toggle UI dinonaktifkan.
                    </div>
                    @endif

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan Pengaturan Maintenance
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</form> {{-- End form --}}

<!-- ============================== -->
<!-- TAB 5: SYSTEM INFO (read-only)  -->
<!-- ============================== -->
<div class="tab-pane fade" id="systemPanel" role="tabpanel">
    <div class="row g-3">
        <!-- Server Info -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-dark text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-server me-1"></i> Server & Runtime</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <tbody>
                            <tr><td class="fw-bold" style="width: 45%;">PHP Version</td><td><span class="badge bg-primary">{{ $systemInfo['php_version'] }}</span></td></tr>
                            <tr><td class="fw-bold">Laravel Version</td><td><span class="badge bg-danger">{{ $systemInfo['laravel_version'] }}</span></td></tr>
                            <tr><td class="fw-bold">Server</td><td><code class="small">{{ $systemInfo['server_software'] }}</code></td></tr>
                            <tr><td class="fw-bold">Environment</td><td><span class="badge {{ $systemInfo['app_env'] === 'production' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $systemInfo['app_env'] }}</span></td></tr>
                            <tr><td class="fw-bold">Debug Mode</td><td><span class="badge {{ $systemInfo['debug_mode'] === 'ON' ? 'bg-danger' : 'bg-success' }}">{{ $systemInfo['debug_mode'] }}</span></td></tr>
                            <tr><td class="fw-bold">Timezone</td><td>{{ $systemInfo['timezone'] }}</td></tr>
                            <tr><td class="fw-bold">Locale</td><td>{{ $systemInfo['locale'] }}</td></tr>
                            <tr><td class="fw-bold">Cache Driver</td><td><span class="badge bg-info">{{ $systemInfo['cache_driver'] }}</span></td></tr>
                            <tr><td class="fw-bold">Session Driver</td><td><span class="badge bg-info">{{ $systemInfo['session_driver'] }}</span></td></tr>
                            <tr><td class="fw-bold">Database</td><td><span class="badge bg-secondary">{{ $systemInfo['db_driver'] }}</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Database Stats -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-database me-1"></i> Database Statistics</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <tbody>
                            @if(isset($systemInfo['db_error']))
                                <tr><td colspan="2" class="text-danger">{{ $systemInfo['db_error'] }}</td></tr>
                            @else
                                <tr>
                                    <td class="fw-bold" style="width: 45%;">Total Submissions</td>
                                    <td><span class="badge bg-primary fs-6">{{ number_format($systemInfo['total_submissions'] ?? 0) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Total Reviewers</td>
                                    <td><span class="badge bg-success fs-6">{{ number_format($systemInfo['total_reviewers'] ?? 0) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Total Marketing</td>
                                    <td><span class="badge bg-info fs-6">{{ number_format($systemInfo['total_marketing'] ?? 0) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Total PIC</td>
                                    <td><span class="badge bg-warning text-dark fs-6">{{ number_format($systemInfo['total_pic'] ?? 0) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Total Journals</td>
                                    <td><span class="badge bg-secondary fs-6">{{ number_format($systemInfo['total_journals'] ?? 0) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Total Slots</td>
                                    <td><span class="badge bg-dark fs-6">{{ number_format($systemInfo['total_slots'] ?? 0) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Total Settings</td>
                                    <td>{{ $systemInfo['total_settings'] ?? 0 }} records</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Disk Info -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-hdd me-1"></i> Storage</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <tbody>
                            <tr><td class="fw-bold" style="width: 45%;">Storage Path</td><td><code class="small">{{ $systemInfo['storage_path'] ?? 'N/A' }}</code></td></tr>
                            <tr><td class="fw-bold">Disk Free</td><td class="text-success fw-bold">{{ $systemInfo['disk_free'] ?? 'N/A' }}</td></tr>
                            <tr><td class="fw-bold">Disk Total</td><td>{{ $systemInfo['disk_total'] ?? 'N/A' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-lightning-charge me-1"></i> Quick Links</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-gear"></i> Setting Web
                        </a>
                        <a href="{{ route('admin.email-settings.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-envelope-at"></i> Email Settings
                        </a>
                        <a href="{{ route('admin.component-overview') }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-palette"></i> Component Editor
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark btn-sm">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================== -->
<!-- TAB 6: CHANGELOG VIEWER         -->
<!-- ============================== -->
<div class="tab-pane fade" id="changelogPanel" role="tabpanel">
    @if(empty($changelogs))
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-1"></i> Tidak ada file CHANGELOG ditemukan di root project.
        </div>
    @else
        <div class="mb-3 text-muted small">
            <i class="bi bi-journal-code me-1"></i> Ditemukan <strong>{{ count($changelogs) }}</strong> changelog dari project root.
        </div>
        <div class="accordion" id="changelogAccordion">
            @foreach($changelogs as $i => $log)
            <div class="accordion-item border-0 shadow-sm mb-2">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#changelog-{{ $i }}">
                        <div class="d-flex align-items-center gap-2 w-100">
                            <span class="badge bg-primary">{{ $log['date'] }}</span>
                            <strong>{{ $log['title'] }}</strong>
                            <span class="badge bg-light text-muted ms-auto me-3">{{ $log['filename'] }}</span>
                        </div>
                    </button>
                </h2>
                <div id="changelog-{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}"
                     data-bs-parent="#changelogAccordion">
                    <div class="accordion-body">
                        <div class="bg-light p-3 rounded" style="max-height: 500px; overflow-y: auto;">
                            <pre class="mb-0" style="white-space: pre-wrap; font-size: 0.85rem;">{{ $log['content'] }}</pre>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

<!-- ============================== -->
<!-- TAB 7: AUDIT LOG                -->
<!-- ============================== -->
<div class="tab-pane fade" id="auditPanel" role="tabpanel">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-clock-history me-1"></i> Riwayat Perubahan Pengaturan</h6>
            <small class="text-muted">{{ count($auditLogs) }} entri terakhir</small>
        </div>
        <div class="card-body p-0">
            @if($auditLogs->isEmpty())
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Belum ada riwayat perubahan. Jalankan <code>php artisan migrate</code> terlebih dahulu untuk membuat tabel audit log.
                </div>
            @else
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-sm table-striped table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th style="width: 160px;">Waktu</th>
                            <th style="width: 100px;">Aksi</th>
                            <th>Admin</th>
                            <th>Detail</th>
                            <th style="width: 120px;">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditLogs as $log)
                        <tr>
                            <td class="small">
                                <span title="{{ $log->created_at->format('d M Y H:i:s') }}">
                                    {{ $log->created_at->diffForHumans() }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $actionBadge = match($log->action) {
                                        'update'   => 'bg-primary',
                                        'reset'    => 'bg-danger',
                                        'import'   => 'bg-success',
                                        'export'   => 'bg-info',
                                        'schedule' => 'bg-warning text-dark',
                                        default    => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $actionBadge }}">{{ $log->action_label }}</span>
                            </td>
                            <td class="small">{{ $log->admin_name }} <span class="text-muted">({{ $log->admin_guard }})</span></td>
                            <td class="small">
                                @if($log->setting_key)
                                    <code>{{ $log->setting_key }}</code>:
                                    <span class="text-danger">{{ Str::limit($log->old_value ?? '-', 30) }}</span>
                                    <i class="bi bi-arrow-right text-muted mx-1"></i>
                                    <span class="text-success">{{ Str::limit($log->new_value ?? '-', 30) }}</span>
                                @elseif($log->batch_changes)
                                    <a href="#" class="text-decoration-none" data-bs-toggle="collapse" data-bs-target="#batch-{{ $log->id }}">
                                        <i class="bi bi-list-ul me-1"></i>{{ count($log->batch_changes) }} perubahan
                                    </a>
                                    <div class="collapse mt-1" id="batch-{{ $log->id }}">
                                        <div class="bg-light p-2 rounded small" style="max-height: 200px; overflow-y: auto;">
                                            @foreach($log->batch_changes as $bKey => $bVal)
                                            <div class="mb-1">
                                                <code>{{ $bKey }}</code>:
                                                @if(is_array($bVal))
                                                    <span class="text-danger">{{ $bVal['old'] ?? '-' }}</span>
                                                    <i class="bi bi-arrow-right mx-1"></i>
                                                    <span class="text-success">{{ $bVal['new'] ?? '-' }}</span>
                                                @else
                                                    <span class="text-success">{{ $bVal }}</span>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- ============================== -->
<!-- TAB 8: IMPORT / EXPORT          -->
<!-- ============================== -->
<div class="tab-pane fade" id="importexportPanel" role="tabpanel">
    <div class="row g-4">
        <!-- Export -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-download me-1"></i> Export Settings</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Download semua pengaturan fitur sebagai file JSON. Berguna untuk backup atau deploy ke server lain.</p>
                    <ul class="list-unstyled small text-muted mb-3">
                        <li><i class="bi bi-check text-success me-1"></i> Feature toggles</li>
                        <li><i class="bi bi-check text-success me-1"></i> Configurable limits</li>
                        <li><i class="bi bi-check text-success me-1"></i> Role capabilities</li>
                        <li><i class="bi bi-check text-success me-1"></i> Maintenance settings</li>
                    </ul>
                    <a href="{{ route('admin.feature-management.export') }}" class="btn btn-success">
                        <i class="bi bi-download me-1"></i> Download JSON
                    </a>
                </div>
            </div>
        </div>

        <!-- Import -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-upload me-1"></i> Import Settings</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Upload file JSON yang di-export sebelumnya untuk menerapkan pengaturan. Setting yang di-override oleh <code>.env</code> akan dilewati.</p>
                    <form action="{{ route('admin.feature-management.import') }}" method="POST" enctype="multipart/form-data"
                          onsubmit="return confirm('Import akan menimpa pengaturan saat ini. Lanjutkan?')">
                        @csrf
                        <div class="mb-3">
                            <input type="file" class="form-control" name="settings_file" accept=".json,.txt" required>
                            <div class="form-text">File JSON maksimum 1MB.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i> Import JSON
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Environment Overrides Info -->
    @if(!empty($envOverrides))
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0"><i class="bi bi-lock-fill me-1"></i> Environment Overrides (.env)</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Setting berikut dikontrol oleh file <code>.env</code> dan tidak bisa diubah dari UI maupun import.</p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Setting Key</th>
                            <th>Forced Value</th>
                            <th>ENV Variable</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($envOverrides as $key => $value)
                        <tr>
                            <td><code>{{ $key }}</code></td>
                            <td><span class="badge {{ $value === '1' ? 'bg-success' : 'bg-danger' }}">{{ $value === '1' ? 'ON' : 'OFF' }}</span></td>
                            <td class="text-muted small">
                                @php
                                    $envNames = ['maintenance_mode' => 'FORCE_MAINTENANCE', 'fasttrack_enabled' => 'DISABLE_FASTTRACK', 'points_enabled' => 'DISABLE_POINTS', 'leaderboard_enabled' => 'DISABLE_LEADERBOARD', 'email_notifications_enabled' => 'DISABLE_EMAIL'];
                                @endphp
                                <code>{{ $envNames[$key] ?? '?' }}</code>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

</div> {{-- End tab-content --}}

@endsection

@section('scripts')
<script>
function toggleCardOpacity(key, isChecked) {
    const card = document.getElementById('card-' + key);
    if (card) {
        card.classList.toggle('opacity-75', !isChecked);
    }
}

function toggleMaintenanceWarning(isChecked) {
    const warning = document.getElementById('maintenanceWarning');
    if (warning) {
        warning.classList.toggle('d-none', !isChecked);
    }
}

// Update dropdown background based on selected value
function updateCapBg(select) {
    const colorMap = {
        'yes': '#e8f5e9',
        'no': '#fce4ec',
        'read-only': '#fff8e1',
        'partial': '#e3f2fd'
    };
    select.style.backgroundColor = colorMap[select.value] || '#fff';
}

// Copy capabilities from source role to target role
function copyCapabilities(fromRole, toRole) {
    const sourceSelects = document.querySelectorAll(`select[data-role="${fromRole}"]`);
    sourceSelects.forEach(src => {
        const cap = src.dataset.cap;
        const target = document.querySelector(`select[data-role="${toRole}"][data-cap="${cap}"]`);
        if (target) {
            target.value = src.value;
            updateCapBg(target);
        }
    });
}

// Initialize dropdown backgrounds on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.capability-select').forEach(updateCapBg);
});
</script>
@endsection
