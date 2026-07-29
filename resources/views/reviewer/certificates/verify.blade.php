<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Sertifikat Reviewer</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f1ea;
            margin: 0;
            padding: 40px 16px;
            display: flex;
            justify-content: center;
        }
        .card {
            background: #fff;
            max-width: 560px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .card-header {
            padding: 24px 28px;
            color: #fff;
            text-align: center;
        }
        .card-header.valid { background: linear-gradient(135deg, #C9A961, #8B6914); }
        .card-header.invalid { background: #b91c1c; }
        .card-header h1 { margin: 8px 0 0; font-size: 20px; }
        .card-header .icon { font-size: 40px; }
        .card-body { padding: 24px 28px 28px; }
        .row { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
        .row:last-child { border-bottom: none; }
        .label { color: #888; font-size: 13px; flex-shrink: 0; }
        .value { color: #222; font-size: 14px; text-align: right; font-weight: 600; }
        .footer-note { text-align: center; color: #aaa; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        @if($valid)
            <div class="card-header valid">
                <div class="icon">&#10003;</div>
                <h1>Sertifikat Terverifikasi</h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <span class="label">Nama Reviewer</span>
                    <span class="value">{{ $reviewerName }}</span>
                </div>
                <div class="row">
                    <span class="label">Peran</span>
                    <span class="value">{{ $position }}</span>
                </div>
                <div class="row">
                    <span class="label">Artikel</span>
                    <span class="value">{{ $articleTitle }}</span>
                </div>
                <div class="row">
                    <span class="label">Jurnal</span>
                    <span class="value">{{ $namaJurnal }}</span>
                </div>
                <div class="row">
                    <span class="label">Publisher</span>
                    <span class="value">{{ $namaPublisher }}</span>
                </div>
                <div class="row">
                    <span class="label">No. Surat</span>
                    <span class="value">{{ $nomorSurat }}</span>
                </div>
                <div class="row">
                    <span class="label">Tanggal Disetujui</span>
                    <span class="value">{{ $approvedAt?->locale('id')->translatedFormat('d F Y') ?? '-' }}</span>
                </div>
                <p class="footer-note">Review ini tercatat resmi di SIPERA — Sistem Insentif dan Penghargaan Reviewer APJI.</p>
            </div>
        @else
            <div class="card-header invalid">
                <div class="icon">&#10007;</div>
                <h1>Sertifikat Tidak Ditemukan / Belum Valid</h1>
            </div>
            <div class="card-body">
                <p style="text-align:center;color:#555;">
                    Kode pada sertifikat ini tidak cocok dengan data review yang sudah disetujui di sistem.
                    Kalau Anda yakin ini kesalahan, hubungi admin SIPERA.
                </p>
            </div>
        @endif
    </div>
</body>
</html>
