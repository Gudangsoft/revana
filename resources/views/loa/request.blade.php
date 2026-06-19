<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Request LOA — SIPERA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a237e 0%, #283593 60%, #1565c0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .card-loa {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0,0,0,.25);
            max-width: 480px;
            width: 100%;
            overflow: hidden;
        }
        .card-header-loa {
            background: #1a237e;
            color: #fff;
            padding: 24px 28px 20px;
        }
        .card-header-loa .title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: .5px;
        }
        .card-header-loa .subtitle {
            font-size: 12px;
            opacity: .75;
            margin-top: 4px;
        }
        .badge-sipera {
            background: #8B6914;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 20px;
            letter-spacing: .5px;
        }
        .card-body-loa { padding: 28px 28px 24px; }
        .form-label { font-weight: 600; font-size: .9rem; }
        .btn-request {
            background: #1a237e;
            color: #fff;
            border: none;
            width: 100%;
            padding: 12px;
            font-size: 15px;
            font-weight: 700;
            border-radius: 8px;
            transition: background .2s;
        }
        .btn-request:hover { background: #283593; color: #fff; }
        .kode-input {
            font-family: monospace;
            font-size: 1.05rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .hint-box {
            background: #f0f4ff;
            border-left: 3px solid #1a237e;
            border-radius: 0 6px 6px 0;
            padding: 10px 14px;
            font-size: .82rem;
            color: #374151;
        }
        .date-optional {
            border: 1px dashed #adb5bd;
            border-radius: 8px;
            padding: 14px 16px;
            background: #fafafa;
        }
        .date-optional.active {
            border-color: #1a237e;
            background: #f0f4ff;
        }
        .footer-note {
            text-align: center;
            font-size: .78rem;
            color: #9ca3af;
            padding: 0 28px 20px;
        }
    </style>
</head>
<body>
<div class="card-loa">

    {{-- Header --}}
    <div class="card-header-loa">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="title"><i class="bi bi-file-earmark-check-fill me-2"></i>Request LOA</div>
                <div class="subtitle">Letter of Acceptance — SIPERA</div>
            </div>
            <span class="badge-sipera">APRKOM</span>
        </div>
    </div>

    {{-- Body --}}
    <div class="card-body-loa">

        @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-4" style="font-size:.9rem;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <form action="{{ route('loa.request.submit') }}" method="POST" id="loaForm">
            @csrf

            {{-- Kode SIPERA --}}
            <div class="mb-3">
                <label for="kode_submit" class="form-label">Kode Submission SIPERA</label>
                <input type="text"
                       id="kode_submit"
                       name="kode_submit"
                       class="form-control kode-input @error('kode_submit') is-invalid @enderror"
                       value="{{ old('kode_submit') }}"
                       placeholder="SUB2026060001"
                       autocomplete="off"
                       autofocus>
                @error('kode_submit')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="hint-box mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Kode SIPERA ada di email konfirmasi submission Anda. Format: <strong>SUB</strong>YYYYMM0001
                </div>
            </div>

            {{-- Tanggal opsional --}}
            <div class="mb-4">
                <label class="form-label d-flex align-items-center gap-2">
                    Tanggal LOA
                    <span class="badge bg-secondary" style="font-size:.68rem;font-weight:500;">Opsional</span>
                </label>
                <div class="date-optional" id="dateBox">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="useDateSwitch" {{ old('tanggal') ? 'checked' : '' }}>
                            <label class="form-check-label small" for="useDateSwitch" id="swLabel">
                                {{ old('tanggal') ? 'Tanggal dipilih' : 'Gunakan tanggal hari ini' }}
                            </label>
                        </div>
                    </div>
                    <div id="dateInputWrap" style="{{ old('tanggal') ? '' : 'display:none;' }}">
                        <input type="date"
                               name="tanggal"
                               id="tanggalInput"
                               class="form-control"
                               value="{{ old('tanggal', now()->format('Y-m-d')) }}">
                        <div class="form-text mt-1">
                            <i class="bi bi-calendar3 me-1"></i>
                            Tanggal yang akan tercetak di dokumen LOA.
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-request" id="btnSubmit">
                <i class="bi bi-file-earmark-arrow-down me-2"></i>Tampilkan LOA
            </button>
        </form>
    </div>

    <div class="footer-note">
        Dokumen LOA hanya dapat diakses oleh pemilik submission yang sah.<br>
        Kode dirahasiakan — jangan dibagikan ke pihak lain.
    </div>

    <div style="text-align:center;padding:0 28px 20px;border-top:1px solid #f3f4f6;margin-top:4px;">
        <p class="text-muted mb-2" style="font-size:.78rem;">Ingin cek status artikel sekaligus?</p>
        <a href="{{ route('author.portal') }}"
           style="font-size:.82rem;color:#1a237e;text-decoration:none;font-weight:600;">
            <i class="bi bi-file-earmark-person me-1"></i>Buka Portal Penulis →
        </a>
    </div>

</div>

<script>
(function () {
    var sw      = document.getElementById('useDateSwitch');
    var wrap    = document.getElementById('dateInputWrap');
    var lbl     = document.getElementById('swLabel');
    var box     = document.getElementById('dateBox');
    var inp     = document.getElementById('tanggalInput');

    function toggle(on) {
        wrap.style.display = on ? '' : 'none';
        lbl.textContent    = on ? 'Tanggal dipilih' : 'Gunakan tanggal hari ini';
        box.classList.toggle('active', on);
        inp.disabled       = !on;
        if (!on) inp.name  = '';
        else     inp.name  = 'tanggal';
    }

    sw.addEventListener('change', function () { toggle(this.checked); });
    toggle(sw.checked);

    // Uppercase kode input
    var kodeInp = document.getElementById('kode_submit');
    kodeInp.addEventListener('input', function () {
        var pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });

    // Loading state on submit
    document.getElementById('loaForm').addEventListener('submit', function () {
        var btn = document.getElementById('btnSubmit');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memuat LOA...';
        btn.disabled = true;
    });
})();
</script>
</body>
</html>
