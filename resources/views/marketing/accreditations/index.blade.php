@extends('marketing.layouts.app')

@section('title', 'Data Akreditasi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-award"></i> Data Akreditasi Jurnal
    </h4>
    <span class="badge bg-primary fs-6">Total: {{ $accreditations->total() }} akreditasi</span>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        @if($accreditations->count() > 0)
        @include('partials.column-toggle', ['tableId' => 'mktAccredTable', 'columns' => ['Nama Akreditasi', 'Deskripsi', 'Status'], 'columnOffset' => 1])
        <div class="table-responsive">
            <table id="mktAccredTable" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-3" style="width: 50px;">#</th>
                        <th>Nama Akreditasi</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accreditations as $index => $accreditation)
                    <tr>
                        <td class="px-3 text-center">
                            {{ $accreditations->firstItem() + $index }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary bg-opacity-10 p-2 rounded">
                                    <i class="bi bi-award-fill text-primary fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $accreditation->name }}</div>
                                    <small class="text-muted">Point: {{ $accreditation->points }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">{{ $accreditation->description ?: 'Tidak ada deskripsi' }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Aktif
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3 mb-0">Tidak ada data akreditasi</p>
        </div>
        @endif
    </div>
    @if($accreditations->count() > 0)
    <div class="card-footer bg-white">
        @include('partials.per-page-selector', ['paginator' => $accreditations])
    </div>
    @endif
</div>

<!-- Info Card -->
<div class="card mt-4 border-info border-start border-4 shadow-sm">
    <div class="card-body">
        <h6 class="text-info mb-2">
            <i class="bi bi-info-circle-fill"></i> Informasi Akreditasi
        </h6>
        <p class="small mb-0 text-muted">
            Data akreditasi digunakan untuk klasifikasi jurnal. Pastikan jurnal yang Anda submit sudah memiliki akreditasi yang sesuai untuk mendapatkan poin yang optimal.
        </p>
    </div>
</div>

@endsection
