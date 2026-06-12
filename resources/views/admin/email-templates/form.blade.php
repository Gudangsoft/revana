@extends('layouts.app')
@section('title', ($template ? 'Edit' : 'Buat') . ' Template Email - ' . $appSettings['app_name'])
@section('page-title', ($template ? 'Edit' : 'Buat') . ' Template Email')
@section('sidebar')@include('admin.partials.sidebar')@endsection

@section('content')
<style>
.var-chip { cursor: pointer; font-size: 0.72rem; padding: 2px 8px; border-radius: 10px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; display: inline-block; margin: 2px; transition: background 0.15s; }
.var-chip:hover { background: #bae6fd; }
#body-editor { font-family: monospace; font-size: 0.85rem; min-height: 260px; }
.preview-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 16px; min-height: 120px; font-size: 0.85rem; }
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope-paper text-warning"></i>
                        {{ $template ? 'Edit Template: ' . $template->name : 'Buat Template Baru' }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST"
                          action="{{ $template ? route('admin.email-templates.update', $template) : route('admin.email-templates.store') }}">
                        @csrf
                        @if($template) @method('PUT') @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Template <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                       value="{{ old('name', $template?->name) }}" placeholder="e.g. Notifikasi Penugasan Editor 1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Trigger <span class="text-danger">*</span></label>
                                @if($template)
                                    <input type="hidden" name="trigger_key" value="{{ $template->trigger_key }}">
                                    <div class="form-control bg-light">
                                        <code>{{ $template->trigger_key }}</code>
                                        <small class="text-muted ms-2">— {{ $allKeys[$template->trigger_key] ?? '' }}</small>
                                    </div>
                                @else
                                    <select name="trigger_key" class="form-select" required>
                                        <option value="">-- Pilih kapan email dikirim --</option>
                                        @php $assignKeys = array_filter($allKeys, fn($k) => str_starts_with($k,'assign_'), ARRAY_FILTER_USE_KEY); @endphp
                                        @php $validateKeys = array_filter($allKeys, fn($k) => str_starts_with($k,'validate_'), ARRAY_FILTER_USE_KEY); @endphp
                                        <optgroup label="Saat PIC Ditugaskan">
                                            @foreach($assignKeys as $key => $label)
                                                @if(isset($availableKeys[$key]))
                                                    <option value="{{ $key }}" {{ (old('trigger_key', $selectedKey) === $key) ? 'selected' : '' }}>{{ $label }}</option>
                                                @endif
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Saat Tahap Divalidasi">
                                            @foreach($validateKeys as $key => $label)
                                                @if(isset($availableKeys[$key]))
                                                    <option value="{{ $key }}" {{ (old('trigger_key', $selectedKey) === $key) ? 'selected' : '' }}>{{ $label }}</option>
                                                @endif
                                            @endforeach
                                        </optgroup>
                                    </select>
                                @endif
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Subjek Email <span class="text-danger">*</span></label>
                                <input type="text" name="subject" id="subjectInput" class="form-control" required
                                       value="{{ old('subject', $template?->subject) }}"
                                       placeholder="e.g. [REVANA] Penugasan Baru: {nama_tahap} – {nama_artikel}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Isi Email <span class="text-danger">*</span></label>
                                <div class="mb-2">
                                    <small class="text-muted me-1">Klik variabel untuk sisipkan ke isi email:</small><br>
                                    @php
                                        $vars = [
                                            '{nama_artikel}' => 'Judul artikel',
                                            '{kode_submit}' => 'Kode submit',
                                            '{id_artikel}' => 'ID artikel',
                                            '{nama_pic}' => 'Nama PIC yang ditugaskan',
                                            '{email_pic}' => 'Email PIC',
                                            '{nama_tahap}' => 'Label tahap',
                                            '{tanggal}' => 'Tanggal sekarang',
                                            '{username_editor}' => 'Username editor',
                                            '{password_editor}' => 'Password editor',
                                            '{username_reviewer1}' => 'Username reviewer 1',
                                            '{password_reviewer1}' => 'Password reviewer 1',
                                            '{username_reviewer2}' => 'Username reviewer 2',
                                            '{password_reviewer2}' => 'Password reviewer 2',
                                            '{app_name}' => 'Nama aplikasi',
                                        ];
                                    @endphp
                                    @foreach($vars as $var => $hint)
                                        <span class="var-chip" title="{{ $hint }}" onclick="insertVar('{{ $var }}')">{{ $var }}</span>
                                    @endforeach
                                </div>
                                <textarea name="body" id="body-editor" class="form-control" required
                                          rows="12">{{ old('body', $template?->body) }}</textarea>
                                <div class="form-text">
                                    Bisa menggunakan HTML dasar: <code>&lt;b&gt;</code>, <code>&lt;br&gt;</code>, <code>&lt;a href="..."&gt;</code>, <code>&lt;ul&gt;&lt;li&gt;</code>, dll.
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
                                           {{ old('is_active', $template?->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isActive">Template aktif (email akan dikirim)</label>
                                </div>
                            </div>
                        </div>

                        {{-- Preview live --}}
                        <div class="mt-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0">Preview (dengan data contoh)</h6>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="livePreview()">
                                    <i class="bi bi-eye"></i> Refresh Preview
                                </button>
                            </div>
                            <div class="mb-1 small"><strong>Subjek:</strong> <span id="liveSubject" class="text-primary">—</span></div>
                            <div id="liveBody" class="preview-box">Preview akan muncul setelah klik Refresh.</div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> {{ $template ? 'Simpan Perubahan' : 'Buat Template' }}
                            </button>
                            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var lastCursor = 0;
var editor = document.getElementById('body-editor');
var subjectEl = document.getElementById('subjectInput');

editor.addEventListener('click', () => { lastCursor = editor.selectionStart; });
editor.addEventListener('keyup', () => { lastCursor = editor.selectionStart; });

function insertVar(v) {
    var start = editor.selectionStart || lastCursor;
    var end   = editor.selectionEnd   || lastCursor;
    var val   = editor.value;
    editor.value = val.substring(0, start) + v + val.substring(end);
    editor.selectionStart = editor.selectionEnd = start + v.length;
    editor.focus();
}

function livePreview() {
    var sampleVars = {
        'nama_artikel':       'Judul Artikel Contoh – Lorem Ipsum Dolor Sit Amet',
        'kode_submit':        'BKD2024001',
        'id_artikel':         'ART-2024-001',
        'nama_pic':           'Dr. Siti Rahayu, M.Pd',
        'email_pic':          'siti.rahayu@apji.org',
        'nama_tahap':         'Editor 1',
        'tanggal':            new Date().toLocaleString('id-ID'),
        'username_editor':    'editor_user',
        'password_editor':    'pass1234',
        'username_reviewer1': 'rev1_user',
        'password_reviewer1': 'rev1_pass',
        'username_reviewer2': 'rev2_user',
        'password_reviewer2': 'rev2_pass',
        'app_name':           '{{ config("app.name") }}'
    };
    var subj = subjectEl.value;
    var body = editor.value;
    for (var k in sampleVars) {
        var re = new RegExp('\\{' + k + '\\}', 'g');
        subj = subj.replace(re, sampleVars[k]);
        body = body.replace(re, sampleVars[k]);
    }
    document.getElementById('liveSubject').textContent = subj;
    document.getElementById('liveBody').innerHTML = body;
}
</script>
@endsection
