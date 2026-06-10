<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Marketing - REVANA</title>
    @if(isset($appSettings['favicon']) && $appSettings['favicon'])
    <link rel="icon" href="{{ asset('storage/' . $appSettings['favicon']) }}" type="image/x-icon">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header i {
            font-size: 4rem;
            color: #11998e;
        }
        .login-header h2 {
            color: #333;
            margin-top: 10px;
        }
        .btn-marketing {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            color: white;
            padding: 12px;
            font-weight: 600;
        }
        .btn-marketing:hover {
            background: linear-gradient(135deg, #0d7d72 0%, #2bc466 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-megaphone-fill"></i>
            <h2>Marketing Portal</h2>
            <p class="text-muted">REVANA - Jurnal Management</p>
        </div>

        @if(session('error'))
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
        </div>
        @endif

        <form method="POST" action="{{ route('marketing.login.submit') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                @error('email')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" required>
                </div>
                @error('password')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Verifikasi: <strong>{{ $captcha_question }}</strong> = ?</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calculator"></i></span>
                    <input type="number" class="form-control" name="captcha_answer" required placeholder="Ketik jawaban">
                </div>
            </div>
            <button type="submit" class="btn btn-marketing w-100">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>

        <div class="my-3 d-flex align-items-center gap-2">
            <hr class="flex-grow-1 my-0">
            <small class="text-muted px-1">atau</small>
            <hr class="flex-grow-1 my-0">
        </div>

        <a href="{{ route('auth.google.redirect', 'marketing') }}"
           class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2 py-2">
            <svg width="18" height="18" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            <span class="fw-medium text-dark">Login dengan Google</span>
        </a>
        <p class="text-center text-muted mt-1" style="font-size:0.72rem;">
            Hanya email Marketing yang sudah terdaftar yang dapat menggunakan fitur ini.
        </p>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="text-muted small">
                <i class="bi bi-arrow-left"></i> Kembali ke Login Admin
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
