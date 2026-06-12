@extends('layouts.app')
@section('title', 'Hasil Screening — ' . $submission->kode_submit)
@section('page-title', 'Screening Awal Artikel')
@section('sidebar')@include('admin.partials.sidebar')@endsection

@section('content')
@php
use App\Models\ScreeningForm;
@endphp
<style>
.check-ok   { color:#16a34a; font-weight:600; }
.check-no   { color:#dc2626; font-weight:600; }
.check-na   { color:#9ca3af; }
.section-tbl td { font-size:0.82rem; padding: 4px 8px; }
.keputusan-banner { border-radius: 12px; padding: 20px 28px; }
</style>

<div class="container-fluid">
    <div class="d-flex gap-2 mb-3 align-items-center">
        <a href="{{ route('admin.submissions.monitoring') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali ke Monitoring</a>
        @if($screening)
            <a href="{{ route('admin.screenings.edit', [$submission, $screening]) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil"></i> Edit Screening
            </a>
        @else
            <a href="{{ route('admin.screenings.create', $submission) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Mulai Screening
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Info submission --}}
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body py-3">
            <div class="row g-2">
                <div class="col-md-1 text-muted" style="font-size:0.78rem;">Kode</div>
                <div class="col-md-11 fw-semibold"><code>{{ $submission->kode_submit }}</code></div>
                <div class="col-md-1 text-muted" style="font-size:0.78rem;">Judul</div>
                <div class="col-md-11" style="font-size:0.88rem;">{{ $submission->judul_artikel }}</div>
                <div class="col-md-1 text-muted" style="font-size:0.78rem;">Penulis</div>
                <div class="col-md-11" style="font-size:0.88rem;">{{ $submission->nama_penulis ?? '-' }}</div>
            </div>
        </div>
    </div>

    @if(!$screening)
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada data screening untuk artikel ini.
            <a href="{{ route('admin.screenings.create', $submission) }}" class="alert-link">Mulai screening sekarang →</a>
        </div>
    @else

    {{-- Hasil keputusan --}}
    @php
        $kColor = ScreeningForm::keputusanColor($screening->keputusan);
        $passed = $screening->countPassed();
        $total  = collect($definition)->sum(fn($s) => count($s['items']));
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-md-8">
            <div class="keputusan-banner bg-{{ $kColor }} {{ in_array($kColor,['warning']) ? '' : 'text-white' }}">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi {{ $screening->keputusan === 'diterima' ? 'bi-check-circle-fill' : ($screening->keputusan === 'revisi' ? 'bi-arrow-clockwise' : 'bi-x-circle-fill') }} fs-2"></i>
                    <div>
                        <div style="font-size:0.75rem; opacity:0.8;">Keputusan Screening</div>
                        <div class="fw-bold fs-5">{{ ScreeningForm::keputusanLabel($screening->keputusan) }}</div>
                        @if($screening->similarity_score !== null)
                            <small>Similarity: <strong>{{ $screening->similarity_score }}%</strong></small>
                        @endif
                    </div>
                    <div class="ms-auto text-center">
                        <div style="font-size:2rem; font-weight:700; line-height:1;">{{ $passed }}</div>
                        <div style="font-size:0.75rem; opacity:0.8;">/{{ $total }} item</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body py-2 px-3">
                    <div class="small text-muted mb-1"><i class="bi bi-envelope"></i> Email Penulis</div>
                    <div class="fw-semibold" style="font-size:0.85rem;">{{ $screening->recipient_email ?? '—' }}</div>
                    @if($screening->email_sent_at)
                        <span class="badge bg-success mt-1">
                            <i class="bi bi-send-check"></i> Terkirim {{ $screening->email_sent_at->format('d/m/Y H:i') }}
                        </span>
                    @else
                        <div class="mt-2">
                            @if($screening->recipient_email)
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="sendEmailNow()">
                                <i class="bi bi-send"></i> Kirim Email Sekarang
                            </button>
                            @else
                                <small class="text-muted">Isi email penulis dulu</small>
                            @endif
                        </div>
                    @endif
                    <div class="mt-2 small text-muted">
                        Screener: {{ $screening->screener?->name ?? 'Admin' }}<br>
                        {{ $screening->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Checklist hasil --}}
    <div class="row g-3 mb-3">
        @foreach($definition as $secKey => $section)
        @php
            $items = $section['items'];
            $cl    = $screening->checklist ?? [];
            $secPass = collect($items)->keys()->filter(fn($k) => ($cl[$k] ?? null) === true)->count();
        @endphp
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header py-2 d-flex align-items-center justify-content-between">
                    <span style="font-size:0.82rem;"><i class="bi {{ $section['icon'] }} me-1 text-muted"></i><strong>{{ $secKey }}. {{ $section['title'] }}</strong></span>
                    <span class="badge bg-{{ $secPass === count($items) ? 'success' : ($secPass >= count($items)/2 ? 'warning' : 'danger') }}">
                        {{ $secPass }}/{{ count($items) }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm section-tbl mb-0">
                        <tbody>
                        @foreach($items as $k => $label)
                        @php $v = $cl[$k] ?? null; @endphp
                        <tr>
                            <td style="width:24px;" class="{{ $v === true ? 'check-ok' : ($v === false ? 'check-no' : 'check-na') }}">
                                {{ $v === true ? '✓' : ($v === false ? '✗' : '—') }}
                            </td>
                            <td>{{ $label }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Catatan --}}
    @if($screening->catatan)
    <div class="card mb-3">
        <div class="card-header py-2"><strong style="font-size:0.85rem;"><i class="bi bi-pencil-square text-info"></i> Catatan Editor</strong></div>
        <div class="card-body" style="white-space:pre-line; font-size:0.85rem;">{{ $screening->catatan }}</div>
    </div>
    @endif

    @endif
</div>

@if($screening && $screening->recipient_email && !$screening->email_sent_at)
<script>
function sendEmailNow() {
    if (!confirm('Kirim email hasil screening ke {{ $screening->recipient_email }}?')) return;
    fetch('{{ route("admin.screenings.send-email", [$submission, $screening]) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
}
</script>
@endif
@endsection
