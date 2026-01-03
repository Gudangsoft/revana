<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Reviewer - {{ $appSettings['app_name'] }}</title>
    @if($appSettings['favicon'])
    <link rel="icon" href="{{ asset('storage/' . $appSettings['favicon']) }}" type="image/x-icon">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .registration-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 800px;
            margin: 0 auto;
        }

        .card-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .card-body {
            padding: 2.5rem;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
        }

        .required-mark {
            color: #dc2626;
        }

        .form-text {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .alert {
            border-radius: 10px;
        }

        .back-link {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .back-link:hover {
            color: #e0e7ff;
        }

        .password-strength {
            height: 5px;
            border-radius: 3px;
            transition: all 0.3s;
            margin-top: 0.5rem;
        }

        .strength-weak { background: #dc2626; width: 33%; }
        .strength-medium { background: #f59e0b; width: 66%; }
        .strength-strong { background: #10b981; width: 100%; }

        .password-match {
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .match-success { color: #10b981; }
        .match-error { color: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-link">
            <i class="bi bi-arrow-left"></i> Kembali ke Beranda
        </a>

        <div class="registration-card">
            <div class="card-header">
                <div class="logo">
                    @if($appSettings['logo'])
                        <img src="{{ asset('storage/' . $appSettings['logo']) }}" alt="Logo" style="max-height: 60px; margin-bottom: 0.5rem;">
                    @else
                        <i class="bi bi-journal-check"></i>
                    @endif
                    <br>{{ $appSettings['app_name'] }}
                </div>
                <h4 class="mb-0">Form Pendaftaran Reviewer</h4>
                <p class="mb-0 mt-2">Bergabunglah bersama kami sebagai reviewer profesional</p>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    
                    @if(session('whatsapp_url'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="bi bi-whatsapp me-2"></i>
                            <strong>Konfirmasi Pendaftaran</strong><br>
                            Silakan klik tombol di bawah untuk mengirim konfirmasi pendaftaran melalui WhatsApp.
                            <div class="mt-3">
                                <a href="{{ session('whatsapp_url') }}" 
                                   target="_blank" 
                                   class="btn btn-success">
                                    <i class="bi bi-whatsapp"></i> Konfirmasi via WhatsApp
                                </a>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('reviewer-registration.store') }}" method="POST">
                    @csrf

                    <!-- Section 1: Data Pribadi -->
                    <div class="row">
                        <div class="col-12 mb-3">
                            <h5 class="text-primary border-bottom pb-2 mb-3">
                                <i class="bi bi-person-circle"></i> Data Pribadi
                            </h5>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">
                                Nama Lengkap dan Gelar <span class="required-mark">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('full_name') is-invalid @enderror" 
                                   id="full_name" 
                                   name="full_name" 
                                   value="{{ old('full_name') }}"
                                   placeholder="Dr. Ahmad Santoso, M.Si."
                                   required>
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                Email <span class="required-mark">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   placeholder="email@example.com"
                                   required>
                            <small class="text-muted">Email akan digunakan untuk login</small>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="affiliation" class="form-label">
                                Institusi/Perguruan Tinggi <span class="required-mark">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('affiliation') is-invalid @enderror" 
                                   id="affiliation" 
                                   name="affiliation" 
                                   value="{{ old('affiliation') }}"
                                   placeholder="Universitas Indonesia"
                                   required>
                            @error('affiliation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="whatsapp" class="form-label">
                                No WhatsApp <span class="required-mark">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('whatsapp') is-invalid @enderror" 
                                   id="whatsapp" 
                                   name="whatsapp" 
                                   value="{{ old('whatsapp') }}"
                                   placeholder="081234567890"
                                   required>
                            @error('whatsapp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Section 2: Keamanan Akun -->
                    <div class="row mt-4">
                        <div class="col-12 mb-3">
                            <h5 class="text-primary border-bottom pb-2 mb-3">
                                <i class="bi bi-shield-lock"></i> Keamanan Akun
                            </h5>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">
                                Password <span class="required-mark">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Minimal 8 karakter"
                                   required>
                            <div class="password-strength" id="strengthBar"></div>
                            <small class="form-text" id="strengthText">Gunakan kombinasi huruf, angka, dan simbol</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">
                                Ulangi Password <span class="required-mark">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Ketik ulang password"
                                   required>
                            <div class="password-match" id="matchText"></div>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Section 3: Data Akademik -->
                    <div class="row mt-4">
                        <div class="col-12 mb-3">
                            <h5 class="text-primary border-bottom pb-2 mb-3">
                                <i class="bi bi-mortarboard"></i> Data Akademik
                            </h5>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="field_of_study_id" class="form-label">
                                Bidang Ilmu/Keahlian <span class="required-mark">*</span>
                            </label>
                            <select class="form-select @error('field_of_study_id') is-invalid @enderror" 
                                    id="field_of_study_id" 
                                    name="field_of_study_id" 
                                    required>
                                <option value="">-- Pilih Bidang Ilmu --</option>
                                @foreach($fieldOfStudies as $field)
                                    <option value="{{ $field->id }}" {{ old('field_of_study_id') == $field->id ? 'selected' : '' }}>
                                        {{ $field->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('field_of_study_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="sinta_id" class="form-label">
                                ID SINTA <span class="required-mark">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('sinta_id') is-invalid @enderror" 
                                   id="sinta_id" 
                                   name="sinta_id" 
                                   value="{{ old('sinta_id') }}"
                                   placeholder="6012345"
                                   required>
                            @error('sinta_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="scopus_id" class="form-label">
                                ID Scopus <small class="text-muted">(Opsional)</small>
                            </label>
                            <input type="text" 
                                   class="form-control @error('scopus_id') is-invalid @enderror" 
                                   id="scopus_id" 
                                   name="scopus_id" 
                                   value="{{ old('scopus_id') }}"
                                   placeholder="Kosongkan jika tidak ada">
                            @error('scopus_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">
                                Bahasa Artikel yang Bisa Direview <span class="required-mark">*</span>
                            </label>
                            <div class="form-check">
                                <input class="form-check-input @error('article_languages') is-invalid @enderror" 
                                       type="checkbox" 
                                       name="article_languages[]" 
                                       value="Indonesia" 
                                       id="lang_indonesia"
                                       {{ in_array('Indonesia', old('article_languages', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="lang_indonesia">
                                    <i class="bi bi-flag-fill text-danger"></i> Indonesia
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input @error('article_languages') is-invalid @enderror" 
                                       type="checkbox" 
                                       name="article_languages[]" 
                                       value="English" 
                                       id="lang_english"
                                       {{ in_array('English', old('article_languages', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="lang_english">
                                    <i class="bi bi-flag-fill text-primary"></i> English (Inggris)
                                </label>
                            </div>
                            @error('article_languages')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle"></i> Pilih minimal 1 bahasa artikel yang bisa Anda review
                            </small>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send-fill me-2"></i> Kirim Pendaftaran
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Dengan mendaftar, Anda menyetujui untuk menjadi reviewer kami
                        </small>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto dismiss alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            strengthBar.className = 'password-strength';
            
            if (password.length === 0) {
                strengthBar.style.width = '0';
                strengthText.textContent = 'Minimal 8 karakter, gunakan kombinasi huruf, angka, dan simbol';
                strengthText.style.color = '#6b7280';
            } else if (strength <= 1) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Kekuatan Password: Lemah';
                strengthText.style.color = '#dc2626';
            } else if (strength <= 3) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Kekuatan Password: Sedang';
                strengthText.style.color = '#f59e0b';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Kekuatan Password: Kuat';
                strengthText.style.color = '#10b981';
            }

            checkPasswordMatch();
        });

        // Password match checker
        const confirmPassword = document.getElementById('password_confirmation');
        const matchText = document.getElementById('matchText');

        confirmPassword.addEventListener('input', checkPasswordMatch);

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirm = confirmPassword.value;

            if (confirm.length === 0) {
                matchText.textContent = '';
                return;
            }

            if (password === confirm) {
                matchText.innerHTML = '<i class="bi bi-check-circle-fill"></i> Password cocok';
                matchText.className = 'password-match match-success';
            } else {
                matchText.innerHTML = '<i class="bi bi-x-circle-fill"></i> Password tidak cocok';
                matchText.className = 'password-match match-error';
            }
        }
    </script>
</body>
</html>
