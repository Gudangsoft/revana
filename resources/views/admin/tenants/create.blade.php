@extends('layouts.app')

@section('title', 'Tambah Tenant Baru')
@section('page-title', 'Tambah Tenant Baru')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">

    <div class="mb-3">
        <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Tenant
        </a>
    </div>

    {{-- System Check --}}
    <div class="card mb-3 border-secondary" id="systemCheckCard">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="spinner-border spinner-border-sm text-secondary" id="checkSpinner"></div>
                <span id="checkLabel" class="small text-muted">Memeriksa konfigurasi sistem...</span>
                <button class="btn btn-warning btn-sm ms-auto d-none" id="btnSetupDb"
                        data-bs-toggle="modal" data-bs-target="#modalSetupDb">
                    <i class="bi bi-gear-fill me-1"></i>Setup Database Admin
                </button>
            </div>
            <div id="checkDetails" class="mt-2 d-none small"></div>
        </div>
    </div>

    {{-- Form --}}
    <div class="card shadow-sm border-primary">
        <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
            <span><i class="bi bi-building-add me-2"></i><strong>Form Tenant Baru</strong></span>
            <small class="opacity-75">Semua langkah berjalan otomatis</small>
        </div>
        <div class="card-body">
            <form id="tenantForm" novalidate>
                @csrf

                {{-- Institusi & Subdomain --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Nama Institusi <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="nameInput" class="form-control"
                               placeholder="Universitas / Sekolah Tinggi / dll." required>
                        <div class="form-text">Nama ini dipakai di pesan WA dan dashboard.</div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Subdomain <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="subdomain" id="subdomainInput"
                                   class="form-control font-monospace"
                                   placeholder="univ-a" pattern="[a-z0-9\-]+" required>
                            <span class="input-group-text">.apji.org</span>
                        </div>
                        <div class="form-text">
                            DB: <code class="text-primary" id="dbPreview">tenant_...</code>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Kontak</label>
                        <input type="email" name="email" class="form-control"
                               placeholder="kontak@institusi.ac.id">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">No. WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-whatsapp text-success"></i></span>
                            <input type="text" name="phone" class="form-control"
                                   placeholder="628123456789">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Paket <span class="text-danger">*</span></label>
                        <select name="plan" id="planSelect" class="form-select"
                                onchange="updatePlanPreview()" required>
                            @foreach($plans as $key => $plan)
                            <option value="{{ $key }}" {{ $key === 'trial' ? 'selected' : '' }}>
                                {{ $plan['label'] }}
                                ({{ $plan['duration'] ? $plan['duration'] . ' hari' : 'Seumur Hidup' }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Catatan Internal</label>
                        <input type="text" name="notes" class="form-control"
                               placeholder="Catatan tentang klien ini...">
                    </div>
                </div>

                {{-- Plan feature preview --}}
                <div class="alert alert-info py-2 px-3 mb-3 small">
                    <div class="fw-semibold mb-1 small"><i class="bi bi-toggles me-1"></i>Fitur yang aktif pada paket ini:</div>
                    <div id="featureList" class="d-flex flex-wrap gap-1"></div>
                </div>

                {{-- Admin section (collapsible) --}}
                <div class="border rounded p-3 mb-4 bg-light">
                    <button type="button" class="btn btn-link p-0 small text-secondary text-decoration-none"
                            data-bs-toggle="collapse" data-bs-target="#adminSection">
                        <i class="bi bi-person-gear me-1"></i>
                        <strong>Admin Default</strong>
                        <span class="text-muted">(opsional — expand untuk isi)</span>
                        <i class="bi bi-chevron-down ms-2"></i>
                    </button>
                    <div class="collapse" id="adminSection">
                        <hr class="my-2">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Admin</label>
                                <input type="text" name="admin_name" class="form-control form-control-sm"
                                       placeholder="Nama lengkap admin">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email Admin</label>
                                <input type="email" name="admin_email" class="form-control form-control-sm"
                                       placeholder="admin@institusi.ac.id">
                            </div>
                            <div class="col-12">
                                <p class="text-muted mb-0" style="font-size:0.78rem">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Jika email diisi, akun admin dibuat otomatis dengan password acak.
                                    Kredensial dikirim via WA jika nomor tersedia.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-lg" id="btnCreate" onclick="startCreate()">
                        <i class="bi bi-building-add me-2"></i>Buat Tenant Sekarang
                    </button>
                    <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary btn-lg">Batal</a>
                </div>
            </form>
        </div>
    </div>

</div>
</div>

{{-- ═══ PROGRESS MODAL ═══ --}}
<div class="modal fade" id="modalProgress" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bi bi-building-add me-2 text-primary"></i>Membuat Tenant Baru
                </h5>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-3">Proses berjalan otomatis, harap tunggu...</p>
                <div id="progressSteps"></div>
                <div id="progressError" class="alert alert-danger mt-3 d-none small mb-0"></div>
                <div id="progressSuccess" class="alert alert-success mt-3 d-none mb-0">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Tenant berhasil dibuat!</strong> Mengalihkan ke halaman detail...
                </div>
            </div>
            <div class="modal-footer d-none" id="progressFooter">
                <a href="#" id="btnGotoTenant" class="btn btn-primary">
                    <i class="bi bi-arrow-right me-1"></i>Lihat Detail Tenant
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- ═══ SETUP DB ADMIN MODAL ═══ --}}
<div class="modal fade" id="modalSetupDb" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-database-gear me-2 text-warning"></i>Setup Database Admin
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    Masukkan kredensial MySQL yang punya privilege
                    <code>CREATE DATABASE</code> (biasanya <strong>root</strong>).
                    Akan disimpan ke file <code>.env</code> di server — cukup dilakukan sekali.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">MySQL Username</label>
                    <input type="text" id="dbAdminUser" class="form-control" value="root" placeholder="root">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">MySQL Password</label>
                    <div class="input-group">
                        <input type="password" id="dbAdminPass" class="form-control" placeholder="Password MySQL">
                        <button class="btn btn-outline-secondary" type="button"
                                onclick="togglePassVis('dbAdminPass', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div id="setupDbResult"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" onclick="testDbAdmin()">
                    <i class="bi bi-plug me-1"></i>Test Koneksi
                </button>
                <button type="button" class="btn btn-success" onclick="saveDbAdmin()">
                    <i class="bi bi-floppy me-1"></i>Simpan & Aktifkan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const plans    = @json($plans);
const features = @json($features);

// ── Init ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    runSystemCheck();
    updatePlanPreview();
    updateDbPreview();

    document.getElementById('nameInput').addEventListener('input', function () {
        const sub = this.value
            .toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9\s\-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .substring(0, 40);
        document.getElementById('subdomainInput').value = sub;
        updateDbPreview();
    });

    document.getElementById('subdomainInput').addEventListener('input', updateDbPreview);
});

// ── Helpers ───────────────────────────────────────────────────────────────────
function updateDbPreview() {
    const val = document.getElementById('subdomainInput').value.replace(/-/g, '_');
    document.getElementById('dbPreview').textContent = val ? 'tenant_' + val : 'tenant_...';
}

function updatePlanPreview() {
    const plan     = document.getElementById('planSelect').value;
    const active   = plans[plan]?.features || [];
    const list     = document.getElementById('featureList');
    list.innerHTML = '';
    Object.entries(features).forEach(([key, feat]) => {
        const on  = active.includes(key);
        const b   = document.createElement('span');
        b.className = 'badge ' + (on ? 'bg-success' : 'bg-light text-muted border');
        b.textContent = (on ? '✓ ' : '✗ ') + feat.label;
        list.appendChild(b);
    });
}

function togglePassVis(inputId, btn) {
    const inp = document.getElementById(inputId);
    const show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    btn.innerHTML = '<i class="bi bi-eye' + (show ? '-slash' : '') + '"></i>';
}

// ── System Check ──────────────────────────────────────────────────────────────
async function runSystemCheck() {
    const card     = document.getElementById('systemCheckCard');
    const spinner  = document.getElementById('checkSpinner');
    const label    = document.getElementById('checkLabel');
    const details  = document.getElementById('checkDetails');
    const btnSetup = document.getElementById('btnSetupDb');

    try {
        const res  = await fetch('{{ route("admin.tenants.system-check") }}');
        const data = await res.json();

        spinner.classList.add('d-none');

        if (data.all_ok) {
            card.className  = 'card mb-3 border-success';
            label.className = 'small text-success fw-semibold';
            label.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Konfigurasi OK — '
                + data.admin_configured.label
                + ' &nbsp;|&nbsp; <a href="#" onclick="testDbNow(event)" class="text-success">Test koneksi</a>';
        } else {
            card.className  = 'card mb-3 border-warning';
            label.className = 'small text-warning fw-semibold';
            label.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i>DB_ADMIN_USERNAME belum diatur — klik tombol di kanan untuk setup';
            btnSetup.classList.remove('d-none');
        }
    } catch (err) {
        spinner.classList.add('d-none');
        card.className  = 'card mb-3 border-secondary';
        label.className = 'small text-muted';
        label.textContent = 'Tidak dapat memeriksa konfigurasi: ' + err.message;
        btnSetup.classList.remove('d-none');
    }
}

async function testDbNow(e) {
    e.preventDefault();
    const label = document.getElementById('checkLabel');
    label.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menguji koneksi database...';

    try {
        const res  = await fetch('{{ route("admin.tenants.test-current-db") }}', {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        });
        const data = await res.json();
        const card = document.getElementById('systemCheckCard');
        if (data.success) {
            card.className  = 'card mb-3 border-success';
            label.className = 'small text-success fw-semibold';
            label.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Koneksi database admin OK — siap membuat tenant';
        } else {
            card.className  = 'card mb-3 border-danger';
            label.className = 'small text-danger fw-semibold';
            label.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i>' + data.message
                + ' &nbsp;<button class="btn btn-sm btn-warning py-0" data-bs-toggle="modal" data-bs-target="#modalSetupDb">Perbaiki</button>';
        }
    } catch (err) {
        document.getElementById('checkLabel').textContent = 'Test gagal: ' + err.message;
    }
}

// ── Create Wizard ─────────────────────────────────────────────────────────────
const stepMeta = {
    record:   { icon: 'bi-person-plus',    label: 'Menyimpan data tenant' },
    database: { icon: 'bi-database-add',   label: 'Membuat database' },
    migrate:  { icon: 'bi-table',          label: 'Membuat struktur tabel' },
    admin:    { icon: 'bi-person-gear',    label: 'Membuat akun admin' },
    wa:       { icon: 'bi-whatsapp',       label: 'Mengirim notifikasi WA' },
};

function renderSteps(stepData) {
    const el = document.getElementById('progressSteps');
    el.innerHTML = Object.entries(stepMeta).map(([key, meta]) => {
        const step  = stepData ? stepData[key] : null;
        let icon, cls;
        if (!step) {
            icon = '<span class="spinner-border spinner-border-sm text-secondary" style="width:1rem;height:1rem"></span>';
            cls  = 'text-muted';
        } else if (step.ok) {
            icon = '<i class="bi bi-check-circle-fill text-success fs-5"></i>';
            cls  = '';
        } else {
            icon = '<i class="bi bi-x-circle-fill text-danger fs-5"></i>';
            cls  = '';
        }
        const detail = step?.msg ? `<div class="text-muted" style="font-size:0.78rem">${step.msg}</div>` : '';
        return `<div class="d-flex align-items-start gap-2 mb-3">
                    <div class="mt-1" style="min-width:1.2rem;text-align:center">${icon}</div>
                    <div>
                        <div class="fw-semibold small ${cls}">
                            <i class="bi ${meta.icon} me-1 opacity-50"></i>${meta.label}
                        </div>
                        ${detail}
                    </div>
                </div>`;
    }).join('');
}

async function startCreate() {
    const form = document.getElementById('tenantForm');
    if (!form.reportValidity()) return;

    const modal = new bootstrap.Modal(document.getElementById('modalProgress'));
    modal.show();

    document.getElementById('progressError').classList.add('d-none');
    document.getElementById('progressSuccess').classList.add('d-none');
    document.getElementById('progressFooter').classList.add('d-none');
    renderSteps(null);

    try {
        const res  = await fetch('{{ route("admin.tenants.store-ajax") }}', {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body:    new FormData(form),
        });
        const data = await res.json();

        renderSteps(data.steps || {});

        if (data.success) {
            document.getElementById('progressSuccess').classList.remove('d-none');
            document.getElementById('btnGotoTenant').href = data.redirect;
            document.getElementById('progressFooter').classList.remove('d-none');
            setTimeout(() => { window.location = data.redirect; }, 2500);
        } else {
            const errEl = document.getElementById('progressError');
            errEl.classList.remove('d-none');
            if (res.status === 422 && data.errors) {
                errEl.innerHTML = '<strong>Validasi gagal:</strong><ul class="mb-0 mt-1">'
                    + Object.values(data.errors).flat().map(e => `<li>${e}</li>`).join('')
                    + '</ul>';
            } else {
                errEl.textContent = 'Satu atau lebih langkah gagal. Lihat detail di atas, lalu coba lagi dari halaman Detail Tenant.';
            }
            document.getElementById('progressFooter').classList.remove('d-none');
            if (data.tenant_id) {
                document.getElementById('btnGotoTenant').href =
                    '{{ url("admin/tenants") }}/' + data.tenant_id;
            }
        }
    } catch (e) {
        renderSteps({});
        const errEl = document.getElementById('progressError');
        errEl.classList.remove('d-none');
        errEl.textContent = 'Request gagal: ' + e.message;
        document.getElementById('progressFooter').classList.remove('d-none');
    }
}

// ── DB Admin Setup ────────────────────────────────────────────────────────────
async function testDbAdmin() {
    const resultEl = document.getElementById('setupDbResult');
    resultEl.innerHTML = '<div class="text-center py-2"><span class="spinner-border spinner-border-sm"></span> Menghubungkan...</div>';

    const res  = await fetch('{{ route("admin.tenants.test-db-admin") }}', {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify({
            db_admin_username: document.getElementById('dbAdminUser').value,
            db_admin_password: document.getElementById('dbAdminPass').value,
        }),
    });
    const data = await res.json();
    resultEl.innerHTML = data.success
        ? `<div class="alert alert-success py-2 small mb-0"><i class="bi bi-check-circle me-1"></i>${data.message}</div>`
        : `<div class="alert alert-danger py-2 small mb-0"><i class="bi bi-x-circle me-1"></i>${data.message}</div>`;
}

async function saveDbAdmin() {
    const resultEl = document.getElementById('setupDbResult');
    resultEl.innerHTML = '<div class="text-center py-2"><span class="spinner-border spinner-border-sm"></span> Menyimpan...</div>';

    const res  = await fetch('{{ route("admin.tenants.save-db-admin") }}', {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify({
            db_admin_username: document.getElementById('dbAdminUser').value,
            db_admin_password: document.getElementById('dbAdminPass').value,
        }),
    });
    const data = await res.json();

    if (data.success) {
        resultEl.innerHTML = `<div class="alert alert-success py-2 small mb-0">
            <i class="bi bi-check-circle-fill me-1"></i>${data.message}. Sistem akan dicek ulang...
        </div>`;
        setTimeout(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalSetupDb')).hide();
            runSystemCheck();
        }, 1800);
    } else {
        resultEl.innerHTML = `<div class="alert alert-danger py-2 small mb-0">
            <i class="bi bi-x-circle me-1"></i>${data.message}
        </div>`;
    }
}
</script>
@endsection
