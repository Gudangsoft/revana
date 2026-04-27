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
                    @php
                        // Determine status based on production_valid
                        $isPublished = $submission->production_valid == 1;
                        $statusText = $isPublished ? 'TERBIT' : 'DALAM PROSES';
                        $statusClass = $isPublished ? 'success' : 'info';
                        $statusIcon = $isPublished ? 'check-circle-fill' : 'clock-fill';
                    @endphp
                    <div class="status-badge {{ $statusClass }}">
                        <i class="bi bi-{{ $statusIcon }}"></i>
                        {{ $statusText }}
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
            </div>

            <!-- Back Button -->
            <div class="text-center">
                <a href="{{ str_contains(Route::currentRouteName(), 'verify') ? route('verify.index') : route('tracking.index') }}" class="back-btn">
                    <i class="bi bi-arrow-left"></i> Lacak Artikel Lain
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
