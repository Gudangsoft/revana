@extends('layouts.app')

@section('title', 'Master LOA')
@section('page-title', 'Master LOA')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-12">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-check-fill text-primary"></i> Master LOA — Template & Otomatis</span>
                <small class="text-muted">Setting template LOA per jurnal</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="28">No</th>
                                <th>Jurnal</th>
                                <th width="80">Logo</th>
                                <th width="120">Editor</th>
                                <th width="100">Warna</th>
                                <th width="120">LOA Otomatis</th>
                                <th width="130">Kelengkapan</th>
                                <th width="90" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journals as $i => $j)
                            @php
                                $missing = [];
                                if (!$j->kode_singkat)          $missing[] = 'Kode singkat';
                                if (!$j->e_issn)                $missing[] = 'E-ISSN';
                                if (!$j->editor_name)           $missing[] = 'Nama editor';
                                if (!$j->logo_path)             $missing[] = 'Logo';
                                if (!$j->editor_signature_path) $missing[] = 'Tanda tangan';
                                $complete = count($missing) === 0;
                            @endphp
                            <tr>
                                <td class="text-muted small">{{ $i + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $j->nama_jurnal }}</div>
                                    @if($j->kode_singkat)
                                    <span class="badge" style="background:{{ $j->primary_color ?? '#1A237E' }}; font-size:.7rem;">
                                        {{ $j->kode_singkat }}
                                    </span>
                                    @endif
                                    @if($j->e_issn)
                                    <small class="text-muted ms-1">E-ISSN: {{ $j->e_issn }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($j->logo_path)
                                        <img src="{{ Storage::url($j->logo_path) }}" height="40" width="40"
                                             style="border-radius:50%;border:2px solid #ddd;object-fit:cover;" alt="Logo">
                                    @else
                                        <div style="width:40px;height:40px;border-radius:50%;
                                                    background:{{ $j->primary_color ?? '#1A237E' }};
                                                    display:flex;align-items:center;justify-content:center;
                                                    color:#fff;font-size:11px;font-weight:bold;">
                                            {{ strtoupper(substr($j->kode_singkat ?: $j->nama_jurnal, 0, 2)) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($j->editor_name)
                                    <div class="small">{{ $j->editor_name }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">{{ $j->editor_title }}</div>
                                    @else
                                    <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1 align-items-center">
                                        <div style="width:22px;height:22px;border-radius:4px;
                                                    background:{{ $j->primary_color ?? '#1A237E' }};
                                                    border:1px solid rgba(0,0,0,.1);"
                                             title="Header: {{ $j->primary_color ?? '#1A237E' }}"></div>
                                        <div style="width:22px;height:22px;border-radius:4px;
                                                    background:{{ $j->secondary_color ?? '#8B6914' }};
                                                    border:1px solid rgba(0,0,0,.1);"
                                             title="Aksen: {{ $j->secondary_color ?? '#8B6914' }}"></div>
                                    </div>
                                </td>
                                <td>
                                    @if($j->loa_auto_send)
                                        <span class="badge bg-success" style="font-size:.72rem;">
                                            <i class="bi bi-lightning-charge-fill"></i> Aktif
                                        </span>
                                        <div class="text-muted" style="font-size:.68rem;margin-top:2px;">
                                            @php
                                            $labels = \App\Http\Controllers\Admin\LoaMasterController::TRIGGER_OPTIONS;
                                            @endphp
                                            {{ Str::after($labels[$j->loa_auto_trigger ?? 'manual'] ?? 'Manual', 'Setelah ') }}
                                        </div>
                                    @else
                                        <span class="badge bg-secondary" style="font-size:.72rem;">Manual</span>
                                    @endif
                                </td>
                                <td>
                                    @if($complete)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Lengkap</span>
                                    @else
                                        <span class="badge bg-warning text-dark"
                                              title="{{ implode(', ', $missing) }} belum diisi"
                                              data-bs-toggle="tooltip">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            {{ count($missing) }} kurang
                                        </span>
                                        <div class="text-muted" style="font-size:.68rem;margin-top:2px;">
                                            {{ implode(', ', array_slice($missing, 0, 2)) }}{{ count($missing) > 2 ? '…' : '' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.loa-master.edit', $j) }}"
                                       class="btn btn-sm btn-outline-primary" title="Setting LOA">
                                        <i class="bi bi-gear"></i> Setting
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Belum ada jurnal aktif.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Info panel --}}
        <div class="card mt-3 border-0" style="background:#f8fafc;">
            <div class="card-body py-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex gap-3 align-items-start">
                            <i class="bi bi-1-circle-fill text-primary fs-4 flex-shrink-0"></i>
                            <div>
                                <div class="fw-semibold small">Setup Template</div>
                                <div class="text-muted" style="font-size:.8rem;">
                                    Klik <strong>Setting</strong> per jurnal untuk isi kode singkat, E-ISSN, editor, warna, logo, dan tanda tangan.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-3 align-items-start">
                            <i class="bi bi-2-circle-fill text-success fs-4 flex-shrink-0"></i>
                            <div>
                                <div class="fw-semibold small">Aktifkan LOA Otomatis</div>
                                <div class="text-muted" style="font-size:.8rem;">
                                    Pilih trigger (Production / Validator / Published). LOA email dikirim otomatis ke penulis saat step tersebut selesai.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-3 align-items-start">
                            <i class="bi bi-3-circle-fill text-warning fs-4 flex-shrink-0"></i>
                            <div>
                                <div class="fw-semibold small">Cetak LOA Manual</div>
                                <div class="text-muted" style="font-size:.8rem;">
                                    Buka detail submission → klik tombol <strong>LOA</strong> → Print / Save PDF dari browser.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
    new bootstrap.Tooltip(el);
});
</script>
@endpush
