<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking LOA - SIPERA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .tracking-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .tracking-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .tracking-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .tracking-header h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .tracking-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        .tracking-body {
            padding: 40px 30px;
        }
        .search-input-group {
            position: relative;
        }
        .search-input-group input {
            padding: 20px 60px 20px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        .search-input-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        .search-input-group button {
            position: absolute;
            right: 8px;
            top: 8px;
            padding: 12px 25px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
        }
        .search-input-group button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .info-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
        }
        .info-card i {
            font-size: 2rem;
            opacity: 0.8;
        }
        .example-code {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            margin-top: 20px;
        }
        .example-code code {
            color: #667eea;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .floating-icon {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="tracking-container">
            <div class="tracking-card">
                <div class="tracking-header">
                    <div class="floating-icon mb-3">
                        <i class="bi bi-search" style="font-size: 3rem;"></i>
                    </div>
                    <h1>Tracking LOA</h1>
                    <p>Sistem Pelacakan Letter of Acceptance</p>
                </div>
                <div class="tracking-body">
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ Route::currentRouteName() == 'verify.index' ? route('verify.search') : route('tracking.search') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold mb-2">
                                <i class="bi bi-qr-code"></i> Masukkan Kode LOA atau Kode Submit
                            </label>
                            <div class="search-input-group">
                                <input type="text" 
                                       name="kode_loa" 
                                       class="form-control" 
                                       placeholder="Contoh: SUB2026010001SIPERA" 
                                       required
                                       autofocus>
                                <button type="submit" class="btn">
                                    <i class="bi bi-search"></i> Lacak
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="info-card">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bi bi-info-circle-fill"></i>
                            <div>
                                <h6 class="fw-bold mb-2">Informasi:</h6>
                                <small>
                                    • Masukkan kode LOA atau kode submit artikel Anda<br>
                                    • Kode bersifat case-insensitive<br>
                                    • Anda akan melihat status dan progress artikel
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="example-code">
                        <small class="text-muted d-block mb-2">Contoh format kode:</small>
                        <code>SUB2026010001SIPERA</code>
                        <small class="text-muted d-block mt-1">atau</small>
                        <code>SUB2026010001</code>
                    </div>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i> Data Anda aman dan terenkripsi
                        </small>
                    </div>

                    <hr class="my-4">
                    <div class="text-center">
                        <p class="text-muted small mb-2">Gunakan <strong>Portal Terpadu</strong> — lacak status & unduh LOA dalam satu halaman.</p>
                        <a href="{{ route('tracking.index') }}"
                           class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                            <i class="bi bi-file-earmark-person me-1"></i>
                            Buka Portal Penulis
                        </a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-white text-decoration-none">
                    <i class="bi bi-box-arrow-in-right"></i> Login Admin/PIC
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
