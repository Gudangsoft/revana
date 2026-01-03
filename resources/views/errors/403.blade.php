<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            text-align: center;
            color: white;
            animation: fadeIn 0.6s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .error-code {
            font-size: 120px;
            font-weight: bold;
            text-shadow: 4px 4px 8px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        .error-icon {
            font-size: 100px;
            margin-bottom: 30px;
            animation: swing 1s infinite;
        }
        @keyframes swing {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(15deg); }
            75% { transform: rotate(-15deg); }
        }
        .error-title {
            font-size: 36px;
            font-weight: 600;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .error-message {
            font-size: 18px;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        .btn-home {
            background: white;
            color: #fa709a;
            padding: 12px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            color: #fee140;
        }
        .locks {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        .lock {
            position: absolute;
            font-size: 40px;
            color: rgba(255,255,255,0.1);
            animation: fall 15s infinite;
        }
        @keyframes fall {
            0% { transform: translateY(-100px); opacity: 1; }
            100% { transform: translateY(100vh); opacity: 0; }
        }
        .lock:nth-child(1) { left: 10%; animation-delay: 0s; }
        .lock:nth-child(2) { left: 30%; animation-delay: 3s; }
        .lock:nth-child(3) { left: 50%; animation-delay: 6s; }
        .lock:nth-child(4) { left: 70%; animation-delay: 9s; }
        .lock:nth-child(5) { left: 90%; animation-delay: 12s; }
    </style>
</head>
<body>
    <div class="locks">
        <div class="lock"><i class="bi bi-lock-fill"></i></div>
        <div class="lock"><i class="bi bi-lock-fill"></i></div>
        <div class="lock"><i class="bi bi-lock-fill"></i></div>
        <div class="lock"><i class="bi bi-lock-fill"></i></div>
        <div class="lock"><i class="bi bi-lock-fill"></i></div>
    </div>

    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <div class="error-code">403</div>
        <h1 class="error-title">Akses Ditolak</h1>
        <p class="error-message">
            Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.<br>
            Silakan hubungi administrator jika Anda yakin ini adalah kesalahan.
        </p>
        <a href="{{ url('/') }}" class="btn-home">
            <i class="bi bi-house-door-fill"></i> Kembali ke Beranda
        </a>
    </div>
</body>
</html>
