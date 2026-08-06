@extends('layouts.app')

@section('title', 'Monitoring Akreditasi - ' . $appSettings['app_name'])
@section('page-title', 'Monitoring Akreditasi')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="alert alert-info d-flex align-items-start gap-2 mb-4">
    <i class="bi bi-info-circle-fill mt-1"></i>
    <div>
        Menampilkan jurnal yang sudah terakreditasi beserta sisa masa berlakunya, supaya tim bisa mulai
        menyiapkan dokumen reakreditasi jauh-jauh hari. Jurnal ditandai <strong>"Perlu Bersiap"</strong> kalau
        akreditasinya berakhir dalam 12 bulan ke depan. Isi/ubah tanggal berakhir lewat menu Master LOA per jurnal.
    </div>
</div>

<div class="row mb-4 g-3">
    <div class="col-6 col-md-3">
        <div class="card bg-danger text-white h-100">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold">{{ $stats['expired'] }}</div>
                <div class="small">Sudah Kedaluwarsa</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold">{{ $stats['warning'] }}</div>
                <div class="small">Perlu Bersiap (&le;12 bln)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-secondary text-white h-100">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold">{{ $stats['unknown'] }}</div>
                <div class="small">Belum Diisi Tanggalnya</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold">{{ $stats['safe'] }}</div>
                <div class="small">Aman</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-patch-check-fill"></i> Masa Berlaku Akreditasi per Jurnal</span>
        <span class="badge bg-primary">{{ $journals->count() }} jurnal terakreditasi</span>
    </div>
    <div class="card-body p-0">
        @if($journals->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Belum ada jurnal dengan status akreditasi terisi.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width:40px" class="text-center">No</th>
                        <th>Nama Jurnal</th>
                        <th>Level Akreditasi</th>
                        <th>Berakhir Tanggal</th>
                        <th>Sisa Waktu</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($journals as $i => $row)
                    @php
                        $badgeClass = match($row['status']) {
                            'expired' => 'bg-danger',
                            'warning' => 'bg-warning text-dark',
                            'unknown' => 'bg-secondary',
                            'safe'    => 'bg-success',
                        };
                        $statusLabel = match($row['status']) {
                            'expired' => 'Kedaluwarsa',
                            'warning' => 'Perlu Bersiap',
                            'unknown' => 'Belum Diisi',
                            'safe'    => 'Aman',
                        };
                    @endphp
                    <tr>
                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $row['journal']->nama_jurnal }}</td>
                        <td>{{ $row['journal']->accreditation }}</td>
                        <td>{{ $row['expiresAt']?->translatedFormat('d F Y') ?? '—' }}</td>
                        <td>
                            @if($row['monthsLeft'] === null)
                                <span class="text-muted">—</span>
                            @elseif($row['monthsLeft'] < 0)
                                <span class="text-danger">{{ abs($row['monthsLeft']) }} bulan lalu</span>
                            @else
                                {{ $row['monthsLeft'] }} bulan lagi
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.loa-master.edit', $row['journal']) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i> Isi/Ubah Tanggal
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
