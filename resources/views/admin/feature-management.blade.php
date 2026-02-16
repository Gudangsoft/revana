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
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       name="{{ $key }}" id="switch-{{ $key }}" value="1"
                                       {{ $featureSettings[$key] === '1' ? 'checked' : '' }}
                                       onchange="toggleCardOpacity('{{ $key }}', this.checked)">
                            </div>
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
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Capability Matrix -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0"><i class="bi bi-grid-3x3-gap me-1"></i> Capability Matrix per Role</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="bg-light" style="min-width: 200px;">Capability</th>
                            @foreach($roleDefinitions as $roleKey => $role)
                            <th class="text-center bg-{{ $role['color'] }} bg-opacity-10" style="min-width: 100px;">
                                <i class="bi {{ $role['icon'] }} text-{{ $role['color'] }}"></i><br>
                                <small class="fw-bold">{{ $role['label'] }}</small>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Collect all capabilities
                            $allCapabilities = [];
                            foreach ($roleDefinitions as $role) {
                                foreach (array_keys($role['capabilities']) as $cap) {
                                    if (!in_array($cap, $allCapabilities)) {
                                        $allCapabilities[] = $cap;
                                    }
                                }
                            }
                        @endphp
                        @foreach($allCapabilities as $capability)
                        <tr>
                            <td><small>{{ $capability }}</small></td>
                            @foreach($roleDefinitions as $roleKey => $role)
                            @php
                                $val = $role['capabilities'][$capability] ?? false;
                                // If value is a string starting with 'feat_', it's a dynamic toggle
                                $isDynamic = is_string($val) && str_starts_with($val, 'feat_');
                                $featureKey = $isDynamic ? str_replace('feat_', '', $val) : null;
                                $isOn = $isDynamic ? ($featureSettings[$featureKey] ?? '0') === '1' : $val;
                            @endphp
                            <td class="text-center" style="background: {{ $val === true ? '#e8f5e9' : ($val === false ? '#fce4ec' : ($val === 'read-only' ? '#fff8e1' : ($isOn ? '#e3f2fd' : '#f3e5f5'))) }};">
                                @if($val === true)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @elseif($val === false)
                                    <i class="bi bi-x-circle text-danger"></i>
                                @elseif($val === 'read-only')
                                    <i class="bi bi-eye text-warning"></i>
                                    <br><small class="text-muted" style="font-size: 0.65rem;">Read Only</small>
                                @elseif($isDynamic)
                                    @if($isOn)
                                        <i class="bi bi-toggle-on text-primary fs-5"></i>
                                        <br><small class="text-primary" style="font-size: 0.6rem;">ON</small>
                                    @else
                                        <i class="bi bi-toggle-off text-muted fs-5"></i>
                                        <br><small class="text-danger" style="font-size: 0.6rem;">OFF</small>
                                    @endif
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light small">
            <span class="me-3"><i class="bi bi-check-circle-fill text-success"></i> Selalu aktif</span>
            <span class="me-3"><i class="bi bi-x-circle text-danger"></i> Tidak tersedia</span>
            <span class="me-3"><i class="bi bi-eye text-warning"></i> Read Only</span>
            <span class="me-3"><i class="bi bi-toggle-on text-primary"></i> Dynamic (bisa diubah di Feature Toggles)</span>
        </div>
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

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan Pengaturan Maintenance
                    </button>
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
</script>
@endsection
