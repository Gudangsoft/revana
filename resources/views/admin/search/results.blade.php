@extends('layouts.app')

@section('title', 'Hasil Pencarian')
@section('page-title', 'Hasil Pencarian')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        {{-- Search bar ulang --}}
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body py-3">
                <form action="{{ route('admin.search') }}" method="GET" class="d-flex gap-2">
                    <input type="text" name="q" class="form-control form-control-lg"
                           value="{{ $q }}" placeholder="Cari nama penulis, ID artikel, judul, kode submit, atau jurnal…"
                           autofocus>
                    <button class="btn btn-primary px-4" type="submit">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </form>
            </div>
        </div>

        @if($tooShort)
        <div class="alert alert-warning">
            <i class="bi bi-info-circle"></i> Masukkan minimal <strong>2 karakter</strong> untuk mulai pencarian.
        </div>
        @elseif($results->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-search text-muted" style="font-size:3rem;"></i>
                <p class="mt-3 text-muted mb-0">Tidak ada hasil untuk <strong>"{{ $q }}"</strong></p>
            </div>
        </div>
        @else
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    <i class="bi bi-list-ul text-primary"></i>
                    Ditemukan <span class="text-primary">{{ $results->count() }}</span> hasil untuk
                    "<strong>{{ $q }}</strong>"
                    @if($results->count() === 50)
                        <small class="text-muted">(menampilkan 50 pertama)</small>
                    @endif
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:140px;">Kode Submit</th>
                                <th>Judul & Penulis</th>
                                <th>Jurnal</th>
                                <th style="width:120px;">Status</th>
                                <th style="width:110px;">Tanggal</th>
                                <th style="width:70px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $s)
                            <tr>
                                <td>
                                    <code class="text-primary fw-bold">{{ $s->kode_submit }}</code>
                                    @if($s->program_type)
                                        <br><span class="badge bg-secondary" style="font-size:.65rem;">{{ strtoupper($s->program_type) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark" style="max-width:320px;">
                                        {{ Str::limit($s->judul_artikel, 70) }}
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-person"></i> {{ $s->nama_penulis }}
                                        @if($s->id_artikel)
                                            &nbsp;·&nbsp;<code>{{ $s->id_artikel }}</code>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <small>{{ $s->journalSlot?->journalMaster?->nama_jurnal ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $s->status_badge_class }}">{{ $s->status_label }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $s->created_at->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('admin.submissions.show', $s) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
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
</div>
@endsection
