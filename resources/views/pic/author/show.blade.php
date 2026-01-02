@extends('pic.layouts.app')

@section('title', 'Detail Artikel')
@section('page-title', 'Detail Artikel')

@section('sidebar')
    <a href="{{ route('pic.author.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('pic.author.create') }}" class="nav-link">
        <i class="bi bi-plus-circle"></i> Input Artikel Baru
    </a>
@endsection

@section('content')
<div class="mb-3">
    <a href="{{ route('pic.author.dashboard') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-journal-text"></i> Informasi Artikel
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="30%">Slot</th>
                        <td><span class="badge bg-info">Slot {{ $journal->slot }}</span></td>
                    </tr>
                    <tr>
                        <th>Volume</th>
                        <td>{{ $journal->volume ? 'Volume ' . $journal->volume : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Judul</th>
                        <td>{{ $journal->title }}</td>
                    </tr>
                    <tr>
                        <th>Link Jurnal</th>
                        <td>
                            @if($journal->link)
                                <a href="{{ $journal->link }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="bi bi-link-45deg"></i> Buka Link
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($journal->status == 'PENDING')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($journal->status == 'PROCESSING')
                                <span class="badge bg-info">Diproses</span>
                            @elseif($journal->status == 'COMPLETED')
                                <span class="badge bg-success">Selesai</span>
                            @else
                                <span class="badge bg-secondary">{{ $journal->status }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Username Author</th>
                        <td><code>{{ $journal->author_username }}</code></td>
                    </tr>
                    <tr>
                        <th>Password Author</th>
                        <td><code>{{ $journal->author_password }}</code></td>
                    </tr>
                    <tr>
                        <th>Tanggal Input</th>
                        <td>{{ $journal->created_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <i class="bi bi-person"></i> Informasi PIC
            </div>
            <div class="card-body">
                <div>
                    <strong>PIC Author (Input Data):</strong><br>
                    <i class="bi bi-person"></i> {{ $journal->picAuthor->name ?? '-' }} (Anda)
                </div>
            </div>
        </div>
    </div>
</div>

@if($journal->reviewAssignments->count() > 0)
<div class="card">
    <div class="card-header bg-secondary text-white">
        <i class="bi bi-clipboard-check"></i> Status Review
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Reviewer</th>
                        <th>Status</th>
                        <th>Tanggal Ditugaskan</th>
                        <th>Tanggal Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($journal->reviewAssignments as $assignment)
                    <tr>
                        <td>{{ $assignment->reviewer->name }}</td>
                        <td>
                            @if($assignment->status == 'PENDING')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($assignment->status == 'ACCEPTED')
                                <span class="badge bg-info">Diterima</span>
                            @elseif($assignment->status == 'ON_PROGRESS')
                                <span class="badge bg-primary">Dikerjakan</span>
                            @elseif($assignment->status == 'SUBMITTED')
                                <span class="badge bg-success">Sudah Submit</span>
                            @elseif($assignment->status == 'APPROVED')
                                <span class="badge bg-success">Disetujui</span>
                            @elseif($assignment->status == 'REJECTED')
                                <span class="badge bg-danger">Ditolak</span>
                            @elseif($assignment->status == 'REVISION')
                                <span class="badge bg-warning">Revisi</span>
                            @else
                                <span class="badge bg-secondary">{{ $assignment->status }}</span>
                            @endif
                        </td>
                        <td>{{ $assignment->created_at->format('d M Y') }}</td>
                        <td>{{ $assignment->completed_at ? $assignment->completed_at->format('d M Y') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
