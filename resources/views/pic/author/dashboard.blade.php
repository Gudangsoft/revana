@extends('pic.layouts.app')

@section('title', 'Dashboard PIC Author')
@section('page-title', 'Dashboard PIC Author')

@section('sidebar')
    <a href="{{ route('pic.author.dashboard') }}" class="nav-link active">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('pic.author.create') }}" class="nav-link">
        <i class="bi bi-plus-circle"></i> Input Artikel Baru
    </a>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-info-circle"></i> Selamat Datang, {{ auth()->guard('pic')->user()->name }}</h5>
                <p class="card-text">Anda login sebagai <strong>PIC Author</strong>. Gunakan sistem ini untuk menginput data artikel jurnal.</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <a href="{{ route('pic.author.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Input Artikel Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-journal-text"></i> Daftar Artikel yang Diinput
    </div>
    <div class="card-body">
        @if($journals->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Jurnal</th>
                        <th>Terbitan</th>
                        <th>Reviewer</th>
                        <th>Institusi</th>
                        <th>Hasil</th>
                        <th>Tanggal Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($journals as $journal)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $journal->title }}</td>
                        <td>
                            @if($journal->link)
                                <a href="{{ $journal->link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-link-45deg"></i> Buka Link
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($journal->picMarketing)
                                <i class="bi bi-person"></i> {{ $journal->picMarketing->name }}
                            @else
                                -
                            @endif
                        </td>
                        <td>-</td>
                        <td>-</td>
                        <td>{{ $journal->created_at->format('d M Y') }}</td>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $journal->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('pic.author.show', $journal) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
            <p class="text-muted mt-3">Belum ada artikel yang diinput.</p>
            <a href="{{ route('pic.author.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Input Artikel Pertama
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
