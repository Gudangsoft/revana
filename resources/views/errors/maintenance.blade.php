<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - SIPERA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .maintenance-container {
            text-align: center;
            color: white;
            animation: fadeIn 0.6s ease-in;
            max-width: 600px;
            padding: 40px 20px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .maintenance-icon {
            font-size: 100px;
            margin-bottom: 30px;
            animation: spin 4s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .maintenance-title {
            font-size: 2rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        .maintenance-message {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon"><i class="bi bi-gear-wide-connected"></i></div>
        <div class="maintenance-title">Sedang Dalam Pemeliharaan</div>
        <div class="maintenance-message">
            {{ $message ?? 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.' }}
        </div>
        <div class="mt-3">
            <small class="opacity-75">SIPERA &mdash; Sistem Informasi Peer Review Artikel</small>
        </div>
    </div>
</body>
</html>
