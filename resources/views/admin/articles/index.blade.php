@extends('layouts.app')

@section('title', ' - Kelola Artikel')
@section('page-title', 'Kelola Artikel')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-file-earmark-text-fill"></i> Daftar Artikel</span>
            <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Artikel
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="articlesTable">
                    <thead class="table-light">
                        <tr>
                            <th width="3%">No</th>
                            <th width="8%">No. Artikel</th>
                            <th width="15%">Judul</th>
                            <th width="10%">Author</th>
                            <th width="10%">Jurnal</th>
                            <th width="8%">Marketing</th>
                            <th width="8%">PIC</th>
                            <th width="8%">Status</th>
                            <th width="10%">Tgl Submit</th>
                            <th width="8%">Link</th>
                            <th width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($articles as $index => $article)
                        <tr>
                            <td class="text-center">{{ $index + 1 + ($articles->currentPage() - 1) * $articles->perPage() }}</td>
                            <td><strong>{{ $article->article_number }}</strong></td>
                            <td>{{ Str::limit($article->title, 50) }}</td>
                            <td>
                                <div>{{ $article->author_name }}</div>
                                @if($article->author_phone)
                                <small class="text-muted">
                                    <i class="bi bi-whatsapp"></i> {{ $article->author_phone }}
                                </small>
                                @endif
                            </td>
                            <td>
                                <small>{{ $article->journal->title ?? '-' }}</small>
                            </td>
                            <td><small>{{ $article->marketing ?? '-' }}</small></td>
                            <td><small>{{ $article->pic ?? '-' }}</small></td>
                            <td>
                                @php
                                    $statusColors = [
                                        'SUBMITTED' => 'primary',
                                        'REVIEW' => 'info',
                                        'REVISION' => 'warning',
                                        'COPYEDITING' => 'secondary',
                                        'PRODUCTION' => 'dark',
                                        'PUBLISHED' => 'success',
                                        'REJECTED' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$article->status] ?? 'secondary' }}">
                                    {{ $article->status }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $article->submission_date ? $article->submission_date->format('d M Y') : '-' }}</small>
                            </td>
                            <td>
                                @if($article->submit_link)
                                <a href="{{ $article->submit_link }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Link Submit">
                                    <i class="bi bi-link-45deg"></i>
                                </a>
                                @endif
                                @if($article->turnitin_link)
                                <a href="{{ $article->turnitin_link }}" target="_blank" class="btn btn-sm btn-outline-info" title="Turnitin">
                                    <i class="bi bi-file-check"></i>
                                </a>
                                @endif
                                @if($article->loa_link)
                                <a href="{{ $article->loa_link }}" target="_blank" class="btn btn-sm btn-outline-success" title="LOA">
                                    <i class="bi bi-award"></i>
                                </a>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.articles.show', $article) }}" class="btn btn-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.articles.destroy', $article) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus artikel ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2">Belum ada data artikel</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('partials.per-page-selector', ['paginator' => $articles, 'default' => 20])
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#articlesTable').DataTable({
        "paging": false,
        "searching": true,
        "ordering": true,
        "info": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        }
    });
});
</script>
@endsection
