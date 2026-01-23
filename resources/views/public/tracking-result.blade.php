<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Tracking - {{ $submission->kode_loa }} - SIPERA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .result-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .result-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .result-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .result-header h2 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 10px;
        }
        .status-badge.success {
            background: #43e97b;
            color: white;
        }
        .status-badge.warning {
            background: #f093fb;
            color: white;
        }
        .status-badge.info {
            background: #4facfe;
            color: white;
        }
        .status-badge.danger {
            background: #f5576c;
            color: white;
        }
        .info-section {
            padding: 30px;
        }
        .info-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            width: 200px;
            font-weight: 600;
            color: #666;
        }
        .info-value {
            flex: 1;
            color: #333;
        }
        .progress-section {
            padding: 30px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        .progress-title {
            font-weight: bold;
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.2rem;
        }
        .timeline {
            position: relative;
            padding-left: 40px;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 30px;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -28px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e0e0e0;
        }
        .timeline-item.completed::before {
            background: #43e97b;
        }
        .timeline-item.active::before {
            background: #4facfe;
        }
        .timeline-dot {
            position: absolute;
            left: -35px;
            top: 5px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #e0e0e0;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #e0e0e0;
        }
        .timeline-item.completed .timeline-dot {
            background: #43e97b;
            box-shadow: 0 0 0 2px #43e97b;
        }
        .timeline-item.active .timeline-dot {
            background: #4facfe;
            box-shadow: 0 0 0 2px #4facfe;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        .timeline-content {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .timeline-content h6 {
            margin-bottom: 5px;
            color: #333;
            font-weight: 600;
        }
        .timeline-content small {
            color: #999;
        }
        .back-btn {
            background: white;
            color: #667eea;
            border: 2px solid white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .back-btn:hover {
            background: transparent;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="result-container">
            <!-- Header Card -->
            <div class="result-card">
                <div class="result-header">
                    <i class="bi bi-check-circle" style="font-size: 3rem;"></i>
                    <h2>Artikel Ditemukan!</h2>
                    <div class="status-badge {{ $submission->status == 'PUBLISHED' ? 'success' : ($submission->status == 'REJECTED' ? 'danger' : 'info') }}">
                        <i class="bi bi-{{ $submission->status == 'PUBLISHED' ? 'check-circle-fill' : ($submission->status == 'REJECTED' ? 'x-circle-fill' : 'clock-fill') }}"></i>
                        {{ str_replace('_', ' ', $submission->status) }}
                    </div>
                </div>

                <!-- Article Info -->
                <div class="info-section">
                    <div class="info-row">
                        <div class="info-label">
                            <i class="bi bi-qr-code"></i> Kode LOA
                        </div>
                        <div class="info-value">
                            <code class="bg-success text-white px-3 py-1 rounded fs-5">{{ $submission->kode_loa }}</code>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">
                            <i class="bi bi-file-text"></i> Kode Submit
                        </div>
                        <div class="info-value">
                            <code class="bg-primary text-white px-3 py-1 rounded">{{ $submission->kode_submit }}</code>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">
                            <i class="bi bi-journal-text"></i> Judul Artikel
                        </div>
                        <div class="info-value">
                            <strong>{{ $submission->judul_artikel }}</strong>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">
                            <i class="bi bi-person"></i> Penulis
                        </div>
                        <div class="info-value">
                            {{ $submission->nama_penulis }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">
                            <i class="bi bi-book"></i> Jurnal
                        </div>
                        <div class="info-value">
                            {{ $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-' }}
                            @if($submission->journalSlot)
                            <br><small class="text-muted">{{ $submission->journalSlot->bulan }}/{{ $submission->journalSlot->tahun }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">
                            <i class="bi bi-calendar"></i> Tanggal Submit
                        </div>
                        <div class="info-value">
                            {{ $submission->tanggal_submit?->format('d F Y') ?? '-' }}
                        </div>
                    </div>
                    @if($submission->marketing)
                    <div class="info-row">
                        <div class="info-label">
                            <i class="bi bi-briefcase"></i> Marketing
                        </div>
                        <div class="info-value">
                            {{ $submission->marketing->nama }}
                            @if($submission->marketing->telp)
                            <br><small class="text-muted"><i class="bi bi-telephone"></i> {{ $submission->marketing->telp }}</small>
                            @endif
                        </div>
                    </div>
                    @endif
                    @if($submission->link_publish)
                    <div class="info-row">
                        <div class="info-label">
                            <i class="bi bi-link-45deg"></i> Link Publish
                        </div>
                        <div class="info-value">
                            <a href="{{ $submission->link_publish }}" target="_blank" class="text-decoration-none">
                                <i class="bi bi-box-arrow-up-right"></i> Lihat Artikel
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Progress Timeline -->
                <div class="progress-section">
                    <div class="progress-title">
                        <i class="bi bi-bar-chart-steps"></i> Progress Artikel
                    </div>
                    <div class="timeline">
                        @php
                            // Stage 1: Submit
                            $submitComplete = $submission->petugas_submit_id ? true : false;
                            
                            // Stage 2: Editor 1
                            $editor1Complete = $submission->editor1_valid == 1;
                            
                            // Stage 3: Author 1
                            $author1Complete = $submission->author1_valid == 1;
                            
                            // Stage 4: Editor 2
                            $editor2Complete = $submission->editor2_valid == 1;
                            
                            // Stage 5: Reviewer (both must complete)
                            $reviewer1Complete = $submission->reviewer1_valid == 1;
                            $reviewer2Complete = $submission->reviewer2_valid == 1;
                            $reviewerComplete = $reviewer1Complete && $reviewer2Complete;
                            
                            // Stage 6: Editor 3 (optional)
                            $editor3Assigned = $submission->petugas_editor3_id ? true : false;
                            $editor3Complete = $submission->editor3_valid == 1;
                            
                            // Stage 7: Author 2 (optional)
                            $author2Assigned = $submission->petugas_author2_id ? true : false;
                            $author2Complete = $submission->author2_valid == 1;
                            
                            // Stage 8: Production (final)
                            $productionComplete = $submission->production_valid == 1;
                            
                            // Determine current active stage
                            $currentStage = '';
                            if (!$submitComplete) $currentStage = 'submit';
                            elseif (!$editor1Complete) $currentStage = 'editor1';
                            elseif (!$author1Complete) $currentStage = 'author1';
                            elseif (!$editor2Complete) $currentStage = 'editor2';
                            elseif (!$reviewerComplete) $currentStage = 'reviewer';
                            elseif ($editor3Assigned && !$editor3Complete) $currentStage = 'editor3';
                            elseif ($author2Assigned && !$author2Complete) $currentStage = 'author2';
                            elseif (!$productionComplete) $currentStage = 'production';
                            else $currentStage = 'published';
                        @endphp
                        
                        <!-- Stage 1: Submit -->
                        <div class="timeline-item {{ $submitComplete ? 'completed' : 'active' }}">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6>
                                    @if($submitComplete)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-arrow-right-circle-fill text-info"></i>
                                    @endif
                                    Submit Artikel
                                </h6>
                                <small>
                                    @if($submitComplete)
                                        <strong>Selesai</strong> oleh {{ $submission->petugasSubmit->nama ?? '-' }}
                                        @if($submission->tanggal_submit)
                                        <br>{{ $submission->tanggal_submit->format('d/m/Y') }}
                                        @endif
                                    @else
                                        Menunggu proses submit
                                    @endif
                                </small>
                            </div>
                        </div>

                        <!-- Stage 2: Editor 1 -->
                        <div class="timeline-item {{ $editor1Complete ? 'completed' : ($currentStage == 'editor1' ? 'active' : '') }}">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6>
                                    @if($editor1Complete)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @elseif($currentStage == 'editor1')
                                        <i class="bi bi-arrow-right-circle-fill text-info"></i>
                                    @else
                                        <i class="bi bi-circle text-muted"></i>
                                    @endif
                                    Editor 1 - Proofreading
                                </h6>
                                <small>
                                    @if($editor1Complete)
                                        <strong>Selesai</strong> oleh {{ $submission->petugasEditor1->nama ?? '-' }}
                                        @if($submission->editor1_validated_at)
                                        <br>{{ $submission->editor1_validated_at->format('d/m/Y H:i') }}
                                        @endif
                                    @elseif($submission->petugas_editor1_id)
                                        Ditugaskan: {{ $submission->petugasEditor1->nama }}
                                    @else
                                        Menunggu
                                    @endif
                                </small>
                            </div>
                        </div>

                        <!-- Stage 3: Author 1 -->
                        <div class="timeline-item {{ $author1Complete ? 'completed' : ($currentStage == 'author1' ? 'active' : '') }}">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6>
                                    @if($author1Complete)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @elseif($currentStage == 'author1')
                                        <i class="bi bi-arrow-right-circle-fill text-info"></i>
                                    @else
                                        <i class="bi bi-circle text-muted"></i>
                                    @endif
                                    Author 1 - Revisi Penulis
                                </h6>
                                <small>
                                    @if($author1Complete)
                                        <strong>Selesai</strong> oleh {{ $submission->petugasAuthor1->nama ?? '-' }}
                                        @if($submission->author1_validated_at)
                                        <br>{{ $submission->author1_validated_at->format('d/m/Y H:i') }}
                                        @endif
                                    @elseif($submission->petugas_author1_id)
                                        Ditugaskan: {{ $submission->petugasAuthor1->nama }}
                                    @else
                                        Menunggu
                                    @endif
                                </small>
                            </div>
                        </div>

                        <!-- Stage 4: Editor 2 -->
                        <div class="timeline-item {{ $editor2Complete ? 'completed' : ($currentStage == 'editor2' ? 'active' : '') }}">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6>
                                    @if($editor2Complete)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @elseif($currentStage == 'editor2')
                                        <i class="bi bi-arrow-right-circle-fill text-info"></i>
                                    @else
                                        <i class="bi bi-circle text-muted"></i>
                                    @endif
                                    Editor 2 - Editing Lanjutan
                                </h6>
                                <small>
                                    @if($editor2Complete)
                                        <strong>Selesai</strong> oleh {{ $submission->petugasEditor2->nama ?? '-' }}
                                        @if($submission->editor2_validated_at)
                                        <br>{{ $submission->editor2_validated_at->format('d/m/Y H:i') }}
                                        @endif
                                    @elseif($submission->petugas_editor2_id)
                                        Ditugaskan: {{ $submission->petugasEditor2->nama }}
                                    @else
                                        Menunggu
                                    @endif
                                </small>
                            </div>
                        </div>

                        <!-- Stage 5: Reviewers -->
                        <div class="timeline-item {{ $reviewerComplete ? 'completed' : ($currentStage == 'reviewer' ? 'active' : '') }}">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6>
                                    @if($reviewerComplete)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @elseif($currentStage == 'reviewer')
                                        <i class="bi bi-arrow-right-circle-fill text-info"></i>
                                    @else
                                        <i class="bi bi-circle text-muted"></i>
                                    @endif
                                    Review - Peer Review
                                </h6>
                                <small>
                                    @if($reviewerComplete)
                                        <strong>Selesai</strong>
                                        @if($submission->reviewer1_validated_at || $submission->reviewer2_validated_at)
                                        <br>
                                        @endif
                                    @endif
                                    
                                    <!-- Reviewer 1 -->
                                    @if($submission->petugas_reviewer1_id)
                                        <div class="mt-1">
                                            @if($reviewer1Complete)
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @else
                                                <i class="bi bi-clock text-warning"></i>
                                            @endif
                                            Reviewer 1: {{ $submission->petugasReviewer1->nama }}
                                            @if($submission->reviewer1_validated_at)
                                            ({{ $submission->reviewer1_validated_at->format('d/m/Y') }})
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <!-- Reviewer 2 -->
                                    @if($submission->petugas_reviewer2_id)
                                        <div class="mt-1">
                                            @if($reviewer2Complete)
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @else
                                                <i class="bi bi-clock text-warning"></i>
                                            @endif
                                            Reviewer 2: {{ $submission->petugasReviewer2->nama }}
                                            @if($submission->reviewer2_validated_at)
                                            ({{ $submission->reviewer2_validated_at->format('d/m/Y') }})
                                            @endif
                                        </div>
                                    @endif
                                    
                                    @if(!$submission->petugas_reviewer1_id && !$submission->petugas_reviewer2_id)
                                        Menunggu
                                    @endif
                                </small>
                            </div>
                        </div>

                        <!-- Stage 6: Editor 3 (Optional) -->
                        @if($editor3Assigned)
                        <div class="timeline-item {{ $editor3Complete ? 'completed' : ($currentStage == 'editor3' ? 'active' : '') }}">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6>
                                    @if($editor3Complete)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @elseif($currentStage == 'editor3')
                                        <i class="bi bi-arrow-right-circle-fill text-info"></i>
                                    @else
                                        <i class="bi bi-circle text-muted"></i>
                                    @endif
                                    Editor 3 - Final Editing <span class="badge bg-info">Opsional</span>
                                </h6>
                                <small>
                                    @if($editor3Complete)
                                        <strong>Selesai</strong> oleh {{ $submission->petugasEditor3->nama ?? '-' }}
                                        @if($submission->editor3_validated_at)
                                        <br>{{ $submission->editor3_validated_at->format('d/m/Y H:i') }}
                                        @endif
                                    @else
                                        Ditugaskan: {{ $submission->petugasEditor3->nama }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        @endif

                        <!-- Stage 7: Author 2 (Optional) -->
                        @if($author2Assigned)
                        <div class="timeline-item {{ $author2Complete ? 'completed' : ($currentStage == 'author2' ? 'active' : '') }}">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6>
                                    @if($author2Complete)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @elseif($currentStage == 'author2')
                                        <i class="bi bi-arrow-right-circle-fill text-info"></i>
                                    @else
                                        <i class="bi bi-circle text-muted"></i>
                                    @endif
                                    Author 2 - Revisi Final <span class="badge bg-info">Opsional</span>
                                </h6>
                                <small>
                                    @if($author2Complete)
                                        <strong>Selesai</strong> oleh {{ $submission->petugasAuthor2->nama ?? '-' }}
                                        @if($submission->author2_validated_at)
                                        <br>{{ $submission->author2_validated_at->format('d/m/Y H:i') }}
                                        @endif
                                    @else
                                        Ditugaskan: {{ $submission->petugasAuthor2->nama }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        @endif

                        <!-- Stage 8: Production (Final) -->
                        <div class="timeline-item {{ $productionComplete ? 'completed' : ($currentStage == 'production' ? 'active' : '') }}">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6>
                                    @if($productionComplete)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @elseif($currentStage == 'production')
                                        <i class="bi bi-arrow-right-circle-fill text-info"></i>
                                    @else
                                        <i class="bi bi-circle text-muted"></i>
                                    @endif
                                    Production - Layout & Publish
                                </h6>
                                <small>
                                    @if($productionComplete)
                                        <strong>Selesai</strong> oleh {{ $submission->petugasProduction->nama ?? '-' }}
                                        @if($submission->production_validated_at)
                                        <br>{{ $submission->production_validated_at->format('d/m/Y H:i') }}
                                        @endif
                                        @if($submission->link_publish)
                                        <br><a href="{{ $submission->link_publish }}" target="_blank" class="text-success">
                                            <i class="bi bi-link-45deg"></i> Link Publish
                                        </a>
                                        @endif
                                    @elseif($submission->petugas_production_id)
                                        Ditugaskan: {{ $submission->petugasProduction->nama }}
                                    @else
                                        Menunggu
                                    @endif
                                </small>
                            </div>
                        </div>

                        @if($productionComplete)
                        <!-- Final: Published -->
                        <div class="timeline-item completed">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6>
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <strong>Artikel Dipublikasi</strong>
                                </h6>
                                <small class="text-success">
                                    Proses selesai - Artikel telah dipublikasi
                                </small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="text-center">
                <a href="{{ route('tracking.index') }}" class="back-btn">
                    <i class="bi bi-arrow-left"></i> Lacak Artikel Lain
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
