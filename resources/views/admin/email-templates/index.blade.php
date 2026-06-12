@extends('layouts.app')
@section('title', 'Template Email - ' . $appSettings['app_name'])
@section('page-title', 'Template Email Monitoring')
@section('sidebar')@include('admin.partials.sidebar')@endsection

@section('content')
<style>
.trigger-badge { font-size: 0.7rem; padding: 2px 7px; border-radius: 10px; }
.table-templates td { vertical-align: middle; }
.preview-body { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 16px; font-size: 0.85rem; max-height: 300px; overflow-y: auto; }
</style>

<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-0"><i class="bi bi-envelope-paper-fill text-warning"></i> Template Email Monitoring</h5>
                <small class="text-muted">Email otomatis dikirim saat PIC ditugaskan atau tahap divalidasi</small>
            </div>
            <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Tambah Template
            </a>
        </div>
        <div class="card-body p-0">
            {{-- Status SMTP --}}
            @php
                $smtpOk = config('mail.mailers.smtp.username') && config('mail.mailers.smtp.password');
            @endphp
            @if(!$smtpOk)
            <div class="alert alert-warning m-3 mb-0 d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                <div>
                    <strong>SMTP belum dikonfigurasi.</strong> Email tidak akan terkirim sampai
                    <code>MAIL_USERNAME</code> dan <code>MAIL_PASSWORD</code> diisi di file <code>.env</code> production.
                    <a href="{{ route('admin.email-settings.index') }}" class="ms-1">Buka Pengaturan Email &rarr;</a>
                </div>
            </div>
            @endif

            {{-- Grid trigger keys --}}
            <div class="p-3">
                <div class="row g-2 mb-3">
                    @php
                        $assignKeys   = array_filter($allKeys, fn($k) => str_starts_with($k, 'assign_'),   ARRAY_FILTER_USE_KEY);
                        $validateKeys = array_filter($allKeys, fn($k) => str_starts_with($k, 'validate_'), ARRAY_FILTER_USE_KEY);
                        $templateMap  = $templates->keyBy('trigger_key');
                    @endphp
                    <div class="col-12">
                        <h6 class="text-muted fw-bold mb-2"><i class="bi bi-person-plus"></i> Saat PIC Ditugaskan</h6>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach($assignKeys as $key => $label)
                                @if($templateMap->has($key))
                                    @php $t = $templateMap[$key]; @endphp
                                    <span class="badge {{ $t->is_active ? 'bg-success' : 'bg-secondary' }} trigger-badge" style="cursor:pointer"
                                          onclick="openTemplate('{{ $t->id }}')">
                                        <i class="bi bi-envelope-check"></i> {{ $label }}
                                    </span>
                                @else
                                    <a href="{{ route('admin.email-templates.create', ['trigger_key' => $key]) }}"
                                       class="badge bg-light text-secondary border trigger-badge text-decoration-none">
                                        <i class="bi bi-plus"></i> {{ $label }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                        <h6 class="text-muted fw-bold mb-2"><i class="bi bi-check2-circle"></i> Saat Tahap Divalidasi</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($validateKeys as $key => $label)
                                @if($templateMap->has($key))
                                    @php $t = $templateMap[$key]; @endphp
                                    <span class="badge {{ $t->is_active ? 'bg-primary' : 'bg-secondary' }} trigger-badge" style="cursor:pointer"
                                          onclick="openTemplate('{{ $t->id }}')">
                                        <i class="bi bi-envelope-check"></i> {{ $label }}
                                    </span>
                                @else
                                    <a href="{{ route('admin.email-templates.create', ['trigger_key' => $key]) }}"
                                       class="badge bg-light text-secondary border trigger-badge text-decoration-none">
                                        <i class="bi bi-plus"></i> {{ $label }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel list template yang sudah dibuat --}}
            @if($templates->count())
            <table class="table table-sm table-hover table-templates mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Template</th>
                        <th>Trigger</th>
                        <th>Subjek</th>
                        <th class="text-center">Aktif</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $t)
                    <tr id="row-{{ $t->id }}">
                        <td class="fw-semibold">{{ $t->name }}</td>
                        <td>
                            <code class="small">{{ $t->trigger_key }}</code><br>
                            <small class="text-muted">{{ $allKeys[$t->trigger_key] ?? $t->trigger_key }}</small>
                        </td>
                        <td>
                            {{ Str::limit($t->subject, 55) }}
                            @if($t->attachments_count)
                                <span class="badge bg-secondary ms-1" title="{{ $t->attachments_count }} lampiran">
                                    <i class="bi bi-paperclip"></i> {{ $t->attachments_count }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm {{ $t->is_active ? 'btn-success' : 'btn-secondary' }}"
                                    onclick="toggleActive({{ $t->id }}, this)" title="{{ $t->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                                <i class="bi {{ $t->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                            </button>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-outline-info btn-sm" onclick="previewTemplate({{ $t->id }})" title="Preview">
                                <i class="bi bi-eye"></i>
                            </button>
                            <a href="{{ route('admin.email-templates.edit', $t) }}" class="btn btn-outline-warning btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.email-templates.destroy', $t) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus template ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center text-muted py-4">
                <i class="bi bi-envelope-x fs-2 d-block mb-2"></i>
                Belum ada template. Klik badge <i class="bi bi-plus"></i> di atas untuk membuat.
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal preview --}}
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Preview Email</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-2"><strong>Subjek:</strong> <span id="previewSubject" class="text-primary"></span></div>
                <div id="previewBody" class="preview-body"></div>
                <div id="previewAttachments" class="mt-3 d-none">
                    <small class="text-muted fw-semibold"><i class="bi bi-paperclip"></i> Lampiran:</small>
                    <ul id="previewAttachList" class="mb-0 mt-1 ps-3 small"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewTemplate(id) {
    fetch('/admin/email-templates/' + id + '/preview')
        .then(r => r.json())
        .then(data => {
            document.getElementById('previewSubject').textContent = data.subject;
            document.getElementById('previewBody').innerHTML = data.body;
            var attBox  = document.getElementById('previewAttachments');
            var attList = document.getElementById('previewAttachList');
            if (data.attachments && data.attachments.length) {
                attList.innerHTML = data.attachments.map(a =>
                    '<li><i class="bi bi-file-earmark me-1"></i>' + a.name + ' <span class="text-muted">(' + a.size + ')</span></li>'
                ).join('');
                attBox.classList.remove('d-none');
            } else {
                attBox.classList.add('d-none');
            }
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        });
}

function toggleActive(id, btn) {
    fetch('/admin/email-templates/' + id + '/toggle-active', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const active = data.is_active;
        btn.className = 'btn btn-sm ' + (active ? 'btn-success' : 'btn-secondary');
        btn.querySelector('i').className = 'bi ' + (active ? 'bi-toggle-on' : 'bi-toggle-off');
        btn.title = active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan';
    });
}

function openTemplate(id) {
    location.href = '/admin/email-templates/' + id + '/edit';
}
</script>
@endsection
