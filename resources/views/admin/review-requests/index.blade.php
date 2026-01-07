@extends('layouts.app')

@section('title', ' - Kelola Permintaan Review')
@section('page-title', 'Kelola Permintaan Review')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Daftar Permintaan Review dari Reviewer</h5>
    </div>
    <div class="card-body">
        <!-- Filter Status -->
        <div class="mb-3">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.review-requests.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">
                    Semua
                </a>
                <a href="{{ route('admin.review-requests.index', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') == 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                    Menunggu
                </a>
                <a href="{{ route('admin.review-requests.index', ['status' => 'approved']) }}" class="btn btn-sm {{ request('status') == 'approved' ? 'btn-success' : 'btn-outline-success' }}">
                    Disetujui
                </a>
                <a href="{{ route('admin.review-requests.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ request('status') == 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}">
                    Ditolak
                </a>
            </div>
        </div>

        @if($reviewRequests->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Reviewer</th>
                            <th>Bidang Ilmu</th>
                            <th>Bahasa</th>
                            <th>Jumlah Jurnal</th>
                            <th>Lama Hari</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviewRequests as $request)
                            <tr>
                                <td>{{ $request->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <strong>{{ $request->reviewer->name }}</strong>
                                    @if($request->reviewer->institution)
                                        <br><small class="text-muted">{{ $request->reviewer->institution }}</small>
                                    @endif
                                    <br><small class="text-muted">{{ $request->reviewer->email }}</small>
                                </td>
                                <td>
                                    @if($request->reviewer->fieldOfStudy)
                                        <span class="badge bg-primary">{{ $request->reviewer->fieldOfStudy->name }}</span>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    @if($request->reviewer->article_languages && is_array($request->reviewer->article_languages))
                                        @foreach($request->reviewer->article_languages as $lang)
                                            <span class="badge bg-secondary">{{ $lang }}</span>
                                        @endforeach
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
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
                                    <a href="{{ route('admin.review-requests.show', $request->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $reviewRequests->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                <p class="text-muted mt-3">Tidak ada permintaan review yang tersedia.</p>
            </div>
        @endif
    </div>
</div>
@endsection
