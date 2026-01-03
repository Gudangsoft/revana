<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Sesi Habis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
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
            animation: rotate 4s linear infinite;
        }
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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
            color: #330867;
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
            color: #30cfd0;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="error-code">419</div>
        <h1 class="error-title">Sesi Telah Habis</h1>
        <p class="error-message">
            Maaf, sesi Anda telah berakhir karena tidak aktif terlalu lama.<br>
            Silakan muat ulang halaman dan coba lagi.
        </p>
        <a href="javascript:window.location.reload()" class="btn-home">
            <i class="bi bi-arrow-clockwise"></i> Muat Ulang Halaman
        </a>
    </div>
</body>
</html>
