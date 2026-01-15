@extends('layouts.app')

@section('title', ' - Monitoring Artikel')
@section('page-title', 'Monitoring Progress Artikel')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-graph-up"></i> Monitoring Progress Artikel
            </div>
            <a href="{{ route('admin.articles.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Tambah Artikel
            </a>
        </div>
        <div class="card-body">
            @if($articles->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Belum ada artikel yang terdaftar.
                </div>
            @else
                @foreach($articles as $article)
                    <div class="article-monitoring-card mb-4">
                        <div class="article-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1">
                                        <a href="{{ route('admin.articles.edit', $article) }}" class="text-decoration-none">
                                            {{ $article->article_number }}
                                        </a>
                                    </h5>
                                    <p class="mb-1 text-muted">{{ $article->title }}</p>
                                    <small class="text-muted">
                                        <i class="bi bi-book"></i> {{ $article->journal->title ?? '-' }} | 
                                        <i class="bi bi-person"></i> {{ $article->author_name }}
                                    </small>
                                </div>
                                <div>
                                    <span class="badge bg-{{ $article->status == 'PUBLISHED' ? 'success' : ($article->status == 'REJECTED' ? 'danger' : 'primary') }}">
                                        {{ $article->status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline Progress -->
                        <div class="timeline-container mt-3">
                            <div class="row text-center">
                                <div class="col timeline-step-small {{ $article->submission_completed ? 'completed' : ($article->submission_date ? 'active' : 'pending') }}">
                                    <div class="timeline-icon-small">
                                        <i class="bi bi-upload"></i>
                                    </div>
                                    <div class="timeline-label-small">Submission</div>
                                    <small class="text-muted d-block">{{ $article->submission_date?->format('d/m/Y') ?? '-' }}</small>
                                    @if($article->submission_completed)
                                        <span class="badge bg-success mt-1">✓</span>
                                    @endif
                                </div>
                                <div class="col timeline-step-small {{ $article->review_completed ? 'completed' : ($article->review_start_date ? 'active' : 'pending') }}">
                                    <div class="timeline-icon-small">
                                        <i class="bi bi-search"></i>
                                    </div>
                                    <div class="timeline-label-small">Review</div>
                                    <small class="text-muted d-block">{{ $article->review_end_date?->format('d/m/Y') ?? '-' }}</small>
                                    @if($article->review_completed)
                                        <span class="badge bg-success mt-1">✓</span>
                                    @endif
                                </div>
                                <div class="col timeline-step-small {{ $article->revision_completed ? 'completed' : ($article->revision_start_date ? 'active' : 'pending') }}">
                                    <div class="timeline-icon-small">
                                        <i class="bi bi-pencil"></i>
                                    </div>
                                    <div class="timeline-label-small">Revision</div>
                                    <small class="text-muted d-block">{{ $article->revision_end_date?->format('d/m/Y') ?? '-' }}</small>
                                    @if($article->revision_completed)
                                        <span class="badge bg-success mt-1">✓</span>
                                    @endif
                                </div>
                                <div class="col timeline-step-small {{ $article->acceptance_completed ? 'completed' : ($article->acceptance_date ? 'active' : 'pending') }}">
                                    <div class="timeline-icon-small">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="timeline-label-small">LOA</div>
                                    <small class="text-muted d-block">{{ $article->acceptance_date?->format('d/m/Y') ?? '-' }}</small>
                                    @if($article->acceptance_completed)
                                        <span class="badge bg-success mt-1">✓</span>
                                    @endif
                                </div>
                                <div class="col timeline-step-small {{ $article->copyediting_completed ? 'completed' : ($article->copyediting_start_date ? 'active' : 'pending') }}">
                                    <div class="timeline-icon-small">
                                        <i class="bi bi-file-text"></i>
                                    </div>
                                    <div class="timeline-label-small">Copyediting</div>
                                    <small class="text-muted d-block">{{ $article->copyediting_end_date?->format('d/m/Y') ?? '-' }}</small>
                                    @if($article->copyediting_completed)
                                        <span class="badge bg-success mt-1">✓</span>
                                    @endif
                                </div>
                                <div class="col timeline-step-small {{ $article->production_completed ? 'completed' : ($article->production_start_date ? 'active' : 'pending') }}">
                                    <div class="timeline-icon-small">
                                        <i class="bi bi-gear"></i>
                                    </div>
                                    <div class="timeline-label-small">Production</div>
                                    <small class="text-muted d-block">{{ $article->production_end_date?->format('d/m/Y') ?? '-' }}</small>
                                    @if($article->production_completed)
                                        <span class="badge bg-success mt-1">✓</span>
                                    @endif
                                </div>
                                <div class="col timeline-step-small {{ $article->publication_completed ? 'completed' : ($article->publication_date ? 'active' : 'pending') }}">
                                    <div class="timeline-icon-small">
                                        <i class="bi bi-trophy"></i>
                                    </div>
                                    <div class="timeline-label-small">Published</div>
                                    <small class="text-muted d-block">{{ $article->publication_date?->format('d/m/Y') ?? '-' }}</small>
                                    @if($article->publication_completed)
                                        <span class="badge bg-success mt-1">✓</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="mt-3 text-end">
                            <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil"></i> Edit Progress
                            </a>
                        </div>
                    </div>
                    @if(!$loop->last)
                        <hr>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</div>

<style>
.article-monitoring-card {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #0d6efd;
}

.article-header h5 a {
    color: #0d6efd;
    font-weight: 600;
}

.article-header h5 a:hover {
    color: #0a58ca;
}

.timeline-container {
    position: relative;
    padding: 15px 0;
    background: white;
    border-radius: 8px;
    padding: 20px;
}

.timeline-step-small {
    position: relative;
    padding: 10px 5px;
}

.timeline-step-small::before {
    content: '';
    position: absolute;
    top: 25px;
    left: 0;
    right: 0;
    height: 2px;
    background: #dee2e6;
    z-index: 0;
}

.timeline-step-small:first-child::before {
    left: 50%;
}

.timeline-step-small:last-child::before {
    right: 50%;
}

.timeline-icon-small {
    width: 50px;
    height: 50px;
    margin: 0 auto 8px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    position: relative;
    z-index: 1;
    background: white;
    border: 2px solid #dee2e6;
    color: #6c757d;
}

.timeline-step-small.completed .timeline-icon-small {
    background: #198754;
    border-color: #198754;
    color: white;
}

.timeline-step-small.active .timeline-icon-small {
    background: #ffc107;
    border-color: #ffc107;
    color: white;
    animation: pulse-small 2s infinite;
}

.timeline-step-small.pending .timeline-icon-small {
    background: #f8f9fa;
    border-color: #dee2e6;
    color: #adb5bd;
}

.timeline-label-small {
    font-weight: 600;
    font-size: 12px;
    margin-top: 5px;
}

.timeline-step-small.completed::before {
    background: #198754;
}

.timeline-step-small.active::before {
    background: linear-gradient(to right, #198754, #ffc107);
}

@keyframes pulse-small {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(255, 193, 7, 0);
    }
}

@media (max-width: 768px) {
    .timeline-step-small {
        font-size: 10px;
    }
    
    .timeline-icon-small {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
}
</style>
@endsection
