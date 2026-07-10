@extends('layouts.app')

@section('title', 'Master Kwitansi')
@section('page-title', 'Master Kwitansi')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ── Pengaturan Bendahara per Jurnal ─────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-person-badge"></i> Pengaturan Bendahara per Jurnal
        <span class="text-muted small ms-auto">Nama & TTD penandatangan kwitansi — terpisah dari Editor LOA</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Jurnal</th>
                        <th>Bendahara</th>
                        <th class="text-center">TTD</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $j)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $j->nama_jurnal }}</div>
                            <small class="text-muted">{{ $j->kode_singkat }}</small>
                        </td>
                        <td>{{ $j->bendahara_name ?: '—' }}</td>
                        <td class="text-center">
                            @if($j->bendahara_signature_path)
                                <span class="badge bg-success">Ada</span>
                            @else
                                <span class="badge bg-secondary">Belum</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.kwitansi-master.edit', $j) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Atur Bendahara
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada jurnal aktif.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Cari Submission → Buka Kwitansi ─────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-receipt"></i> Cari Submission untuk Buka Kwitansi
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group" style="max-width:420px;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama penulis / kode submit / judul artikel..."
                       value="{{ $search }}">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Cari
                </button>
                @if($search)
                <a href="{{ route('admin.kwitansi-master.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kode Submit</th>
                        <th>Nama Penulis</th>
                        <th>Judul Artikel</th>
                        <th>Jurnal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $s)
                    <tr>
                        <td><code>{{ $s->kode_submit }}</code></td>
                        <td>{{ $s->nama_penulis }}</td>
                        <td style="max-width:320px;">{{ \Illuminate\Support\Str::limit($s->judul_artikel, 60) }}</td>
                        <td>{{ $s->journalSlot?->journalMaster?->nama_jurnal ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.submissions.kwitansi', $s) }}" class="btn btn-sm btn-primary" target="_blank">
                                <i class="bi bi-eye"></i> Lihat Kwitansi
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            @if($search)
                                Tidak ada submission yang cocok dengan pencarian "{{ $search }}".
                            @else
                                Belum ada submission.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $submissions->links() }}
    </div>
</div>

@endsection
