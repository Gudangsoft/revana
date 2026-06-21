@extends('marketing.layouts.app')

@section('title', 'Master LOA')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-file-earmark-check-fill text-success"></i> Master LOA
        <small class="text-muted fs-6 ms-2">Jurnal yang Anda kelola</small>
    </h4>
    <span class="badge bg-secondary fs-6">{{ $journals->count() }} jurnal</span>
</div>

@if($journals->isEmpty())
<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-journal-x" style="font-size:3.5rem;opacity:.4;"></i>
        <h5 class="mt-3">Belum Ada Jurnal</h5>
        <p>Anda belum memiliki submission di jurnal manapun.</p>
    </div>
</div>
@else
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Jurnal</th>
                        <th>Kode</th>
                        <th>E-ISSN</th>
                        <th>Kota TTD</th>
                        <th>Jabatan Editor</th>
                        <th style="min-width:220px;">Tanggal LOA Default</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($journals as $i => $journal)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($journal->logo_path)
                                <img src="{{ Storage::url($journal->logo_path) }}"
                                     style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;"
                                     alt="Logo">
                                @endif
                                <div>
                                    <div class="fw-semibold">{{ $journal->nama_jurnal }}</div>
                                    @if($journal->publisher)
                                    <small class="text-muted">{{ $journal->publisher }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><code class="small">{{ $journal->kode_singkat ?: '-' }}</code></td>
                        <td><small>{{ $journal->e_issn ?: '-' }}</small></td>
                        <td><small>{{ $journal->kota_ttd ?: '-' }}</small></td>
                        <td><small>{{ $journal->jabatan_editor ?: '-' }}</small></td>
                        <td>
                            <form method="POST"
                                  action="{{ route('marketing.loa-master.update', $journal) }}"
                                  class="d-flex align-items-center gap-2">
                                @csrf
                                <input type="date"
                                       name="loa_tanggal"
                                       class="form-control form-control-sm"
                                       style="width:160px;"
                                       value="{{ $journal->loa_tanggal ? \Carbon\Carbon::parse($journal->loa_tanggal)->toDateString() : '' }}"
                                       title="Kosong = gunakan tanggal hari ini saat buka LOA">
                                <button type="submit" class="btn btn-sm btn-success" title="Simpan">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                @if($journal->loa_tanggal)
                                <button type="submit" name="loa_tanggal" value=""
                                        class="btn btn-sm btn-outline-secondary"
                                        title="Hapus (gunakan hari ini)"
                                        onclick="this.form.querySelector('[name=loa_tanggal]:not([type=hidden])').value=''">
                                    <i class="bi bi-x"></i>
                                </button>
                                @endif
                            </form>
                            @if($journal->loa_tanggal)
                            <div class="form-text mt-1">
                                Default: {{ \Carbon\Carbon::parse($journal->loa_tanggal)->translatedFormat('d F Y') }}
                            </div>
                            @else
                            <div class="form-text text-muted mt-1">Default: hari ini</div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="alert alert-info mt-3 py-2 small">
    <i class="bi bi-info-circle me-1"></i>
    Tanggal LOA Default dipakai sebagai tanggal surat LOA untuk semua artikel di jurnal ini.
    Kosongkan agar LOA memakai tanggal hari ini saat dibuka.
    Tanggal ini juga bisa di-override per artikel di halaman detail artikel.
</div>
@endif
@endsection
