@extends('layouts.app')

@section('title', ' - ' . $appSettings['app_name'])
@section('page-title', 'Daftar Review Assignment')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row mb-3">
    <div class="col">
        <a href="{{ route('admin.assignments.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Assign Reviewer Baru
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card">
    <div class="card-header">
        <i class="bi bi-list-ul"></i> Semua Review Assignments
    </div>
    <div class="card-body">
        @if($assignments->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada assignment yang dibuat.
            <a href="{{ route('admin.assignments.create') }}">Buat assignment pertama</a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="hide-mobile">#</th>
                        <th>Artikel</th>
                        <th class="hide-mobile">Reviewer</th>
                        <th>Status</th>
                        <th class="hide-mobile">Assigned By</th>
                        <th class="hide-mobile">Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignments as $assignment)
                    <tr>
                        <td class="hide-mobile">{{ $assignment->id }}</td>
                        <td>
                            <div class="fw-bold">{{ Str::limit($assignment->article_title ?? 'N/A', 40) }}</div>
                            <small class="text-muted d-block d-md-inline">
                                <span class="badge bg-secondary"><i class="bi bi-translate"></i> {{ $assignment->language ?? 'N/A' }}</span>
                                @if($assignment->deadline)
                                    <i class="bi bi-calendar-event"></i> {{ $assignment->deadline->format('d M Y') }}
                                @endif
                            </small>
                            <div class="d-md-none mt-1">
                                <small class="text-muted">
                                    <i class="bi bi-person"></i> {{ $assignment->reviewer->name }}
                                    @if($assignment->reviewer2)
                                        + {{ $assignment->reviewer2->name }}
                                    @endif
                                </small>
                            </div>
                        </td>
                        <td class="hide-mobile">
                            <div><strong>Reviewer 1:</strong> {{ $assignment->reviewer->name }}</div>
                            <small class="text-muted">{{ $assignment->reviewer->email }}</small>
                            @if($assignment->reviewer2)
                                <div class="mt-1"><strong>Reviewer 2:</strong> {{ $assignment->reviewer2->name }}</div>
                                <small class="text-muted">{{ $assignment->reviewer2->email }}</small>
                            @endif
                        </td>
                        <td>
                            @if($assignment->status === 'pending')
                                <span class="badge bg-warning">
                                    <i class="bi bi-clock"></i> Pending
                                </span>
                            @elseif($assignment->status === 'accepted')
                                <span class="badge bg-info">
                                    <i class="bi bi-check"></i> Accepted
                                </span>
                            @elseif($assignment->status === 'rejected')
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle"></i> Rejected
                                </span>
                            @elseif($assignment->status === 'submitted')
                                <span class="badge bg-primary">
                                    <i class="bi bi-send"></i> Submitted
                                </span>
                            @elseif($assignment->status === 'approved')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Approved
                                </span>
                            @endif
                        </td>
                        <td class="hide-mobile">
                            <small>{{ $assignment->assignedBy->name }}</small>
                        </td>
                        <td class="hide-mobile">
                            <small>{{ $assignment->created_at->format('d M Y') }}</small>
                            <br>
                            <small class="text-muted">{{ $assignment->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.assignments.show', $assignment) }}" 
                                   class="btn btn-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($assignment->status === 'pending')
                                <form action="{{ route('admin.assignments.destroy', $assignment) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus assignment ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $assignments->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Stats Summary -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-muted">Total</h5>
                <h2>{{ $assignments->total() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-warning">Pending</h5>
                <h2>{{ $assignments->where('status', 'pending')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-info">Accepted</h5>
                <h2>{{ $assignments->where('status', 'accepted')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-success">Completed</h5>
                <h2>{{ $assignments->where('status', 'approved')->count() }}</h2>
            </div>
        </div>
    </div>
</div>
@endsection

