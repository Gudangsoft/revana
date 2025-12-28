<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - REVANA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }

        .login-left {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-right {
            padding: 3rem;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="login-card">
                    <div class="row g-0">
                        <div class="col-md-5 login-left">
                            <div class="logo">
                                <i class="bi bi-journal-check"></i><br>REVANA
                            </div>
                            <h4>Review Validation & Analytics</h4>
                            <p class="mb-0">Platform monitoring dan manajemen review jurnal ilmiah dengan sistem point, badge, dan reward.</p>
                        </div>
                        <div class="col-md-7 login-right">
                            <h3 class="mb-4">Login</h3>
                            
                            @if($errors->any())
                            <div class="alert alert-danger">
                                @foreach($errors->all() as $error)
                                    {{ $error }}
                                @endforeach
                            </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" name="remember" id="remember">
                                    <label class="form-check-label" for="remember">
                                        Ingat Saya
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    <i class="bi bi-box-arrow-in-right"></i> Login
                                </button>
                            </form>

                            <div class="mt-4 text-center">
                                <small class="text-muted">
                                    <strong>Demo Accounts:</strong><br>
                                    Admin: admin@revana.com / password<br>
                                    Reviewer: ahmad@revana.com / password
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
