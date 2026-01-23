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
                            $stages = [
                                'SUBMITTED' => 'Artikel Disubmit',
                                'REVIEW' => 'Proses Review',
                                'EDITING' => 'Proses Editing',
                                'LAYOUT' => 'Proses Layout',
                                'PRODUCTION' => 'Proses Production',
                                'PUBLISHED' => 'Artikel Dipublikasi',
                            ];
                            
                            $currentStage = '';
                            if ($submission->status == 'SUBMITTED') $currentStage = 'SUBMITTED';
                            elseif (in_array($submission->status, ['REVIEW_ASSIGNED', 'UNDER_REVIEW', 'REVISION_REQUIRED', 'REVISED'])) $currentStage = 'REVIEW';
                            elseif (in_array($submission->status, ['EDITING', 'EDITING_SUBMITTED', 'EDITING_COMPLETED'])) $currentStage = 'EDITING';
                            elseif (in_array($submission->status, ['LAYOUT', 'LAYOUT_SUBMITTED', 'LAYOUT_COMPLETED'])) $currentStage = 'LAYOUT';
                            elseif (in_array($submission->status, ['PRODUCTION', 'PRODUCTION_SUBMITTED'])) $currentStage = 'PRODUCTION';
                            elseif ($submission->status == 'PUBLISHED') $currentStage = 'PUBLISHED';
                            
                            $stageOrder = array_keys($stages);
                            $currentIndex = array_search($currentStage, $stageOrder);
                        @endphp
                        
                        @foreach($stages as $key => $label)
                            @php
                                $index = array_search($key, $stageOrder);
                                $isCompleted = $index < $currentIndex;
                                $isActive = $key == $currentStage;
                                $isPending = $index > $currentIndex;
                            @endphp
                            <div class="timeline-item {{ $isCompleted ? 'completed' : ($isActive ? 'active' : '') }}">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h6>
                                        @if($isCompleted)
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                        @elseif($isActive)
                                            <i class="bi bi-arrow-right-circle-fill text-info"></i>
                                        @else
                                            <i class="bi bi-circle text-muted"></i>
                                        @endif
                                        {{ $label }}
                                    </h6>
                                    <small>
                                        @if($isCompleted)
                                            Selesai
                                        @elseif($isActive)
                                            Sedang diproses
                                        @else
                                            Menunggu
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endforeach
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
