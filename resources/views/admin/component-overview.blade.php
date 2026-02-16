@extends('layouts.app')

@section('title', 'Component Overview - Admin')
@section('page-title', 'Component Overview & Access Mapping')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-puzzle"></i> Component Overview</h4>
        <p class="text-muted mb-0">Kelola komponen UI yang dipakai bersama di Marketing dan PIC. Ubah 1x, update di semua halaman.</p>
    </div>
</div>

<!-- Shared Components Section -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-boxes"></i> Shared Blade Components</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle"></i> 
            <strong>Cara kerja:</strong> Komponen di bawah ini dipakai bersama oleh halaman Marketing dan PIC. 
            Edit file komponen sekali, perubahan berlaku di semua halaman yang menggunakannya.
        </div>
        
        <div class="row g-3">
            @foreach($sharedComponents as $component)
            <div class="col-md-6">
                <div class="card h-100 border">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-puzzle-fill text-primary"></i> 
                            <code>&lt;x-{{ $component['name'] }}&gt;</code>
                        </h6>
                        <p class="card-text text-muted small mb-2">{{ $component['description'] }}</p>
                        <div class="mb-2">
                            <small class="text-muted">File:</small>
                            <code class="small">resources/views/{{ $component['file'] }}</code>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Usage:</small>
                            <pre class="bg-light p-2 rounded small mb-0"><code>{{ $component['usage'] }}</code></pre>
                        </div>
                        <div>
                            <small class="text-muted">Dipakai di:</small>
                            <div class="mt-1">
                                @foreach($component['usedIn'] as $view)
                                    <span class="badge bg-light text-dark border me-1 mb-1">{{ $view }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Live Component Preview -->
@if($sampleSubmission)
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-eye"></i> Live Preview Komponen</h5>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Preview menggunakan data submission terbaru: 
            <strong>{{ $sampleSubmission->kode_submit }}</strong> - {{ Str::limit($sampleSubmission->judul_artikel, 50) }}
        </p>
        
        <div class="row g-4">
            <!-- Status Badge Preview -->
            <div class="col-md-4">
                <div class="card border h-100">
                    <div class="card-header bg-light">
                        <small class="fw-bold"><i class="bi bi-tag"></i> submission-status</small>
                    </div>
                    <div class="card-body text-center">
                        <p class="small text-muted mb-2">Normal:</p>
                        <x-submission-status :submission="$sampleSubmission" />
                        <hr>
                        <p class="small text-muted mb-2">Small:</p>
                        <x-submission-status :submission="$sampleSubmission" size="small" />
                    </div>
                </div>
            </div>
            
            <!-- Progress Bar Preview -->
            <div class="col-md-4">
                <div class="card border h-100">
                    <div class="card-header bg-light">
                        <small class="fw-bold"><i class="bi bi-bar-chart-steps"></i> submission-progress</small>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">Height 8px:</p>
                        <x-submission-progress :submission="$sampleSubmission" :height="8" />
                        <hr>
                        <p class="small text-muted mb-2">Height 12px:</p>
                        <x-submission-progress :submission="$sampleSubmission" :height="12" />
                        <hr>
                        <p class="small text-muted mb-2">Tanpa text:</p>
                        <x-submission-progress :submission="$sampleSubmission" :show-text="false" />
                    </div>
                </div>
            </div>
            
            <!-- Slot Link Preview -->
            <div class="col-md-4">
                <div class="card border h-100">
                    <div class="card-header bg-light">
                        <small class="fw-bold"><i class="bi bi-link-45deg"></i> slot-link</small>
                    </div>
                    <div class="card-body">
                        @if($sampleSubmission->journalSlot)
                            <p class="small text-muted mb-2">Marketing guard:</p>
                            <x-slot-link :journal-slot="$sampleSubmission->journalSlot" guard="marketing" />
                            <hr>
                            <p class="small text-muted mb-2">PIC guard:</p>
                            <x-slot-link :journal-slot="$sampleSubmission->journalSlot" guard="pic" />
                            <hr>
                            <p class="small text-muted mb-2">Admin guard:</p>
                            <x-slot-link :journal-slot="$sampleSubmission->journalSlot" guard="admin" />
                        @else
                            <p class="text-muted small">Submission ini tidak punya slot jurnal untuk preview</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tracking Table Preview -->
        <div class="mt-4">
            <h6><i class="bi bi-table"></i> Preview: tracking-table</h6>
            <x-tracking-table :submission="$sampleSubmission" />
        </div>
    </div>
</div>
@endif

<!-- Marketing Access Mapping -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-megaphone"></i> Halaman Marketing</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Halaman</th>
                        <th>Deskripsi</th>
                        <th>Komponen Dipakai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($marketingPages as $index => $page)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <i class="bi {{ $page['icon'] }} text-info me-1"></i>
                            <strong>{{ $page['name'] }}</strong>
                        </td>
                        <td><small class="text-muted">{{ $page['description'] }}</small></td>
                        <td>
                            @forelse($page['components'] as $comp)
                                <span class="badge bg-primary me-1">{{ $comp }}</span>
                            @empty
                                <span class="text-muted small">-</span>
                            @endforelse
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- PIC Access Mapping -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="bi bi-person-badge"></i> Halaman PIC</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Halaman</th>
                        <th>Deskripsi</th>
                        <th>Komponen Dipakai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($picPages as $index => $page)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <i class="bi {{ $page['icon'] }} text-warning me-1"></i>
                            <strong>{{ $page['name'] }}</strong>
                        </td>
                        <td><small class="text-muted">{{ $page['description'] }}</small></td>
                        <td>
                            @forelse($page['components'] as $comp)
                                <span class="badge bg-warning text-dark me-1">{{ $comp }}</span>
                            @empty
                                <span class="text-muted small">-</span>
                            @endforelse
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- How To Guide -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="bi bi-book"></i> Panduan Update Komponen</h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <h6><i class="bi bi-1-circle text-primary"></i> Edit Status Badge</h6>
                <p class="small text-muted">Untuk mengubah tampilan badge status (warna, teks, icon):</p>
                <code class="d-block bg-light p-2 rounded small">resources/views/components/submission-status.blade.php</code>
                <p class="small text-muted mt-1">Berlaku otomatis di: Dashboard, Submissions, Monitoring, Detail Slot</p>
            </div>
            <div class="col-md-6">
                <h6><i class="bi bi-2-circle text-primary"></i> Edit Progress Bar</h6>
                <p class="small text-muted">Untuk mengubah tampilan progress bar (warna, tinggi, format):</p>
                <code class="d-block bg-light p-2 rounded small">resources/views/components/submission-progress.blade.php</code>
                <p class="small text-muted mt-1">Berlaku otomatis di: Dashboard, Submissions, Monitoring, Detail Slot</p>
            </div>
            <div class="col-md-6">
                <h6><i class="bi bi-3-circle text-primary"></i> Edit Tracking Table</h6>
                <p class="small text-muted">Untuk mengubah tabel tracking proses review (kolom, urutan tahap, credential):</p>
                <code class="d-block bg-light p-2 rounded small">resources/views/components/tracking-table.blade.php</code>
                <p class="small text-muted mt-1">Berlaku otomatis di: Detail Submission Marketing</p>
            </div>
            <div class="col-md-6">
                <h6><i class="bi bi-4-circle text-primary"></i> Edit Slot Link</h6>
                <p class="small text-muted">Untuk mengubah tampilan link kode slot (format, warna):</p>
                <code class="d-block bg-light p-2 rounded small">resources/views/components/slot-link.blade.php</code>
                <p class="small text-muted mt-1">Berlaku otomatis di: Slot Jurnal Marketing, PIC, Daftar Jurnal</p>
            </div>
        </div>
        
        <hr>
        
        <div class="alert alert-success mb-0">
            <i class="bi bi-lightbulb"></i> 
            <strong>Tips:</strong> Untuk mengubah kalkulasi status/progress (bukan hanya tampilan), edit model di:
            <code>app/Models/Submission.php</code> method <code>getRealStatus()</code>, 
            <code>getProgressPercentageAttribute()</code>, <code>getStatusLabelAttribute()</code>, 
            <code>getStatusBadgeClassAttribute()</code>
        </div>
    </div>
</div>
@endsection
