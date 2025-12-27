@extends('layouts.app')

@section('title', 'Kelola Jurnal - REVANA')
@section('page-title', 'Kelola Jurnal')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('admin.journals.index') }}" class="nav-link active">
        <i class="bi bi-journal-text"></i> Jurnal
    </a>
    <a href="{{ route('admin.assignments.index') }}" class="nav-link">
        <i class="bi bi-clipboard-check"></i> Review Assignments
    </a>
    <a href="{{ route('admin.reviewers.index') }}" class="nav-link">
        <i class="bi bi-people"></i> Reviewers
    </a>
    <a href="{{ route('admin.leaderboard.index') }}" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="{{ route('admin.redemptions.index') }}" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
    <a href="{{ route('admin.points.index') }}" class="nav-link">
        <i class="bi bi-coin"></i> Point Management
    </a>
    <a href="{{ route('admin.rewards.index') }}" class="nav-link">
        <i class="bi bi-trophy"></i> Reward Management
    </a>
    <a href="{{ route('admin.marketings.index') }}" class="nav-link">
        <i class="bi bi-megaphone"></i> Marketing
    </a>
    <a href="{{ route('admin.pics.index') }}" class="nav-link">
        <i class="bi bi-person-badge"></i> PIC
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text"></i> Daftar Jurnal</span>
                <a href="{{ route('admin.journals.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Jurnal
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Judul</th>
                                <th>Akreditasi</th>
                                <th>Points</th>
                                <th class="hide-mobile">Terbitan</th>
                                <th class="hide-mobile">Marketing</th>
                                <th class="hide-mobile">PIC</th>
                                <th>Dibuat Oleh</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journals as $journal)
                            <tr>
                                <td>{{ $loop->iteration + ($journals->currentPage() - 1) * $journals->perPage() }}</td>
                                <td>
                                    <strong>{{ Str::limit($journal->title, 60) }}</strong><br>
                                    <small class="text-muted">
                                        <i class="bi bi-link-45deg"></i>
                                        <a href="{{ $journal->link }}" target="_blank">Lihat Jurnal</a>
                                    </small>
                                </td>
                                <td><span class="badge bg-info">{{ $journal->accreditation }}</span></td>
                                <td><span class="badge bg-success">{{ $journal->points }} pts</span></td>
                                <td class="hide-mobile">
                                    @if($journal->publisher)
                                        <small>{{ Str::limit($journal->publisher, 25) }}</small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td class="hide-mobile">
                                    @if($journal->marketing)
                                        <small>{{ Str::limit($journal->marketing, 25) }}</small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td class="hide-mobile">
                                    @if($journal->pic)
                                        <small>{{ Str::limit($journal->pic, 25) }}</small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>{{ $journal->creator->name }}</td>
                                <td>{{ $journal->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.journals.edit', $journal) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.journals.destroy', $journal) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">Belum ada data jurnal</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $journals->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

