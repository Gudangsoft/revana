@extends('layouts.app')

@section('title', ' - Riwayat Permintaan Review')
@section('page-title', 'Riwayat Permintaan Review')

@section('sidebar')
    @include('reviewer.partials.sidebar')
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Riwayat Permintaan Review Saya</h5>
        <a href="{{ route('reviewer.review-requests.create') }}" class="btn btn-light btn-sm">
            <i class="bi bi-plus-circle"></i> Ajukan Permintaan Baru
        </a>
    </div>
    <div class="card-body">
        @if($reviewRequests->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal Pengajuan</th>
                            <th>Jumlah Jurnal</th>
                            <th>Lama Hari</th>
                            <th>Status</th>
                            <th>Catatan Admin</th>
                            <th>Disetujui Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviewRequests as $request)
                            <tr>
                                <td>{{ $request->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $request->number_of_journals }} jurnal</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $request->number_of_days }} hari</span>
                                </td>
                                <td>
                                    @if($request->status === 'pending')
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock"></i> Menunggu
                                        </span>
                                    @elseif($request->status === 'approved')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Disetujui
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($request->admin_notes)
                                        <small>{{ $request->admin_notes }}</small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    @if($request->approver)
                                        <small>{{ $request->approver->name }}</small>
                                        <br>
                                        <small class="text-muted">{{ $request->approved_at->format('d M Y H:i') }}</small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                            </tr>
                            @if($request->notes)
                                <tr>
                                    <td colspan="6" class="bg-light">
                                        <small><strong>Catatan Anda:</strong> {{ $request->notes }}</small>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                @include('components.simple-pagination', ['paginator' => $reviewRequests])
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                <p class="text-muted mt-3">Belum ada permintaan review yang diajukan.</p>
                <a href="{{ route('reviewer.review-requests.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Ajukan Permintaan Pertama
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
