@extends('layouts.app')

@section('title', 'Panduan Manajemen Tenant')
@section('page-title', 'Panduan Manajemen Tenant')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="container-fluid">

    {{-- Nav atas --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Tenant
            </a>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Tambah Tenant Sekarang
            </a>
        </div>
    </div>

    {{-- Hero --}}
    <div class="rounded-3 mb-4 p-4 text-white shadow-sm"
         style="background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 60%, #7c3aed 100%);">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="small opacity-75 mb-1"><i class="bi bi-book me-1"></i>Dokumentasi Lengkap</div>
                <h3 class="fw-bold mb-2">Panduan Manajemen Tenant SIPERA</h3>
                <p class="opacity-75 mb-0">
                    Pelajari cara mengelola sistem multi-tenant SIPERA: dari membuat klien baru,
                    mengatur fitur, hingga melakukan pemeliharaan database.
                </p>
            </div>
            <div class="col-md-4 text-end d-none d-md-block">
                <i class="bi bi-building-fill-gear" style="font-size:6rem;opacity:0.15;"></i>
            </div>
        </div>
    </div>

    {{-- Daftar Isi --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm sticky-top" style="top:80px;">
                <div class="card-header bg-dark text-white fw-semibold">
                    <i class="bi bi-list-ul me-2"></i>Daftar Isi
                </div>
                <div class="list-group list-group-flush" style="font-size:0.85rem;">
                    <a href="#konsep" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill">1</span> Konsep Dasar
                    </a>
                    <a href="#prasyarat" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill">2</span> Prasyarat Server
                    </a>
                    <a href="#buat-tenant" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <span class="badge bg-success rounded-pill">3</span> Membuat Tenant Baru
                    </a>
                    <a href="#kelola-fitur" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <span class="badge bg-info rounded-pill">4</span> Kelola Fitur
                    </a>
                    <a href="#paket" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark rounded-pill">5</span> Paket & Fitur
                    </a>
                    <a href="#suspend" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <span class="badge bg-danger rounded-pill">6</span> Suspend & Aktifkan
                    </a>
                    <a href="#migrate" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <span class="badge bg-secondary rounded-pill">7</span> Migrasi Database
                    </a>
                    <a href="#hapus" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <span class="badge bg-danger rounded-pill">8</span> Hapus Tenant
                    </a>
                    <a href="#cli" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <span class="badge bg-dark rounded-pill">9</span> Perintah CLI
                    </a>
                    <a href="#faq" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <span class="badge bg-secondary rounded-pill">10</span> FAQ
                    </a>
                </div>
            </div>
        </div>

        {{-- Konten Tutorial --}}
        <div class="col-md-9">

            {{-- 1. Konsep Dasar --}}
            <div class="card border-0 shadow-sm mb-4" id="konsep">
                <div class="card-header d-flex align-items-center gap-2" style="background:#ede9fe;">
                    <span class="badge bg-primary rounded-pill">1</span>
                    <strong>Konsep Dasar Multi-Tenant</strong>
                </div>
                <div class="card-body">
                    <p>Sistem SIPERA menggunakan arsitektur <strong>multi-tenant dengan 1 codebase, beda database</strong>. Artinya:</p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="card border-primary h-100">
                                <div class="card-body text-center p-3">
                                    <i class="bi bi-shield-fill-check text-primary fs-3 mb-2 d-block"></i>
                                    <div class="fw-semibold small">1 Codebase</div>
                                    <div class="text-muted" style="font-size:0.78rem;">Satu instalasi Laravel untuk semua klien</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success h-100">
                                <div class="card-body text-center p-3">
                                    <i class="bi bi-database-fill text-success fs-3 mb-2 d-block"></i>
                                    <div class="fw-semibold small">Beda Database</div>
                                    <div class="text-muted" style="font-size:0.78rem;">Setiap klien punya database terpisah</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-info h-100">
                                <div class="card-body text-center p-3">
                                    <i class="bi bi-toggles text-info fs-3 mb-2 d-block"></i>
                                    <div class="fw-semibold small">Fitur Fleksibel</div>
                                    <div class="text-muted" style="font-size:0.78rem;">Toggle fitur berbeda per klien</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-light rounded p-3 mb-3" style="font-family:monospace;font-size:0.82rem;">
                        <div class="text-muted mb-1">// Struktur domain</div>
                        <div><span class="text-primary">portal.apji.org</span> → Super Admin (sistem ini)</div>
                        <div><span class="text-success">univ-a.apji.org</span> → Klien 1 (DB: tenant_univ_a)</div>
                        <div><span class="text-success">stikes-b.apji.org</span> → Klien 2 (DB: tenant_stikes_b)</div>
                        <div><span class="text-success">kampus-c.apji.org</span> → Klien 3 (DB: tenant_kampus_c)</div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-lightbulb-fill me-2"></i>
                        <strong>Update Otomatis:</strong> Saat kode SIPERA diperbarui, semua klien langsung mendapat versi terbaru. Tidak perlu update satu per satu.
                    </div>
                </div>
            </div>

            {{-- 2. Prasyarat Server --}}
            <div class="card border-0 shadow-sm mb-4" id="prasyarat">
                <div class="card-header d-flex align-items-center gap-2" style="background:#fef9c3;">
                    <span class="badge bg-warning text-dark rounded-pill">2</span>
                    <strong>Prasyarat Server (Setup Sekali di Awal)</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted">Sebelum membuat tenant pertama, pastikan hal-hal berikut sudah dikonfigurasi di server:</p>

                    <div class="row g-3">
                        {{-- MySQL Permission --}}
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-database-fill-gear text-danger fs-5"></i>
                                    <strong>A. Grant Permission MySQL</strong>
                                    <span class="badge bg-danger">Wajib</span>
                                </div>
                                <p class="small text-muted mb-2">MySQL user di <code>.env</code> harus punya hak <code>CREATE DATABASE</code>:</p>
                                <div class="bg-dark text-white rounded p-3 small" style="font-family:monospace;">
                                    <div class="text-warning">-- Jalankan di MySQL / phpMyAdmin / HeidiSQL</div>
                                    <div class="mt-1">GRANT ALL PRIVILEGES ON <span class="text-success">`tenant_%`</span>.* TO <span class="text-cyan">'user_db'</span>@'localhost';</div>
                                    <div>FLUSH PRIVILEGES;</div>
                                    <div class="text-muted mt-1">-- Ganti 'user_db' dengan username di .env (DB_USERNAME)</div>
                                </div>
                                <div class="alert alert-warning mt-2 mb-0 py-2 small">
                                    <i class="bi bi-shield-exclamation me-1"></i>
                                    Permission ini hanya berlaku untuk database berawalan <code>tenant_</code> — tidak menyentuh database lain.
                                </div>
                            </div>
                        </div>

                        {{-- DNS --}}
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-globe text-primary fs-5"></i>
                                    <strong>B. Wildcard DNS</strong>
                                    <span class="badge bg-danger">Wajib</span>
                                </div>
                                <p class="small text-muted mb-2">Tambahkan record DNS wildcard di panel domain (Cloudflare / cPanel / dll):</p>
                                <div class="bg-dark text-white rounded p-3 small" style="font-family:monospace;">
                                    <div class="text-warning">Type &nbsp; Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Value</div>
                                    <div>A &nbsp;&nbsp;&nbsp;&nbsp; <span class="text-success">*.apji.org</span> &nbsp; <span class="text-cyan">123.456.789.0</span> &nbsp; (IP server)</div>
                                </div>
                            </div>
                        </div>

                        {{-- Nginx --}}
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-server text-success fs-5"></i>
                                    <strong>C. Konfigurasi Nginx</strong>
                                    <span class="badge bg-danger">Wajib</span>
                                </div>
                                <p class="small text-muted mb-2">Tambahkan server block untuk wildcard subdomain (1 codebase, semua subdomain):</p>
                                <div class="bg-dark text-white rounded p-3 small" style="font-family:monospace;line-height:1.7;">
                                    <div><span class="text-yellow">server</span> {</div>
                                    <div class="ms-3">listen 80;</div>
                                    <div class="ms-3"><span class="text-green">server_name</span> *.apji.org;</div>
                                    <div class="ms-3"><span class="text-green">root</span> /var/www/sipera/public;</div>
                                    <div class="ms-3">index index.php;</div>
                                    <div class="ms-3 mt-1">location / {</div>
                                    <div class="ms-5">try_files $uri $uri/ /index.php?$query_string;</div>
                                    <div class="ms-3">}</div>
                                    <div class="ms-3 mt-1">location ~ \.php$ {</div>
                                    <div class="ms-5">fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;</div>
                                    <div class="ms-5">fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;</div>
                                    <div class="ms-5">include fastcgi_params;</div>
                                    <div class="ms-3">}</div>
                                    <div>}</div>
                                </div>
                                <div class="small text-muted mt-2">
                                    <i class="bi bi-info-circle me-1"></i>Untuk HTTPS, tambahkan SSL wildcard certificate (Let's Encrypt mendukung wildcard dengan DNS challenge).
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Membuat Tenant Baru --}}
            <div class="card border-0 shadow-sm mb-4" id="buat-tenant">
                <div class="card-header d-flex align-items-center gap-2" style="background:#dcfce7;">
                    <span class="badge bg-success rounded-pill">3</span>
                    <strong>Cara Membuat Tenant Baru</strong>
                </div>
                <div class="card-body">

                    {{-- Alur --}}
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4 p-3 bg-light rounded">
                        @foreach(['Klik "Tambah Tenant"', 'Isi Form', 'Klik "Buat Tenant"', 'Sistem Buat DB', 'Migration Otomatis', 'Selesai ✓'] as $i => $step)
                        <div class="d-flex align-items-center gap-2">
                            @if($i > 0)<i class="bi bi-arrow-right text-muted"></i>@endif
                            <span class="badge {{ $i === 5 ? 'bg-success' : 'bg-primary' }} px-2 py-1">
                                {{ $i+1 }}. {{ $step }}
                            </span>
                        </div>
                        @endforeach
                    </div>

                    {{-- Form fields --}}
                    <h6 class="fw-semibold mb-3">Penjelasan Field Form</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:180px">Field</th>
                                    <th>Keterangan</th>
                                    <th style="width:90px">Wajib?</th>
                                </tr>
                            </thead>
                            <tbody style="font-size:0.85rem;">
                                <tr>
                                    <td class="fw-semibold">Nama Klien / Kontak</td>
                                    <td>Nama PIC atau kontak utama dari institusi. Digunakan sebagai identitas utama tenant di panel super admin.</td>
                                    <td><span class="badge bg-danger">Wajib</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Nama Institusi</td>
                                    <td>Nama resmi universitas / sekolah tinggi / lembaga. Contoh: <em>Universitas Nusantara</em></td>
                                    <td><span class="badge bg-secondary">Opsional</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Email</td>
                                    <td>Email kontak institusi. Untuk keperluan komunikasi dan notifikasi.</td>
                                    <td><span class="badge bg-secondary">Opsional</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">No. WhatsApp</td>
                                    <td>Nomor WA aktif. Format: <code>628123456789</code> (tanpa + atau 0 di awal).</td>
                                    <td><span class="badge bg-secondary">Opsional</span></td>
                                </tr>
                                <tr class="table-warning">
                                    <td class="fw-semibold">Subdomain</td>
                                    <td>
                                        Nama unik yang menjadi alamat akses klien. Hanya huruf kecil, angka, dan tanda hubung (<code>-</code>).
                                        <br>Contoh: <code>univ-a</code> → akan menjadi <strong>univ-a.apji.org</strong>
                                        <br><span class="text-danger small">Tidak bisa diubah setelah dibuat.</span>
                                    </td>
                                    <td><span class="badge bg-danger">Wajib</span></td>
                                </tr>
                                <tr class="table-info">
                                    <td class="fw-semibold">Paket</td>
                                    <td>
                                        Menentukan fitur apa saja yang aktif sejak awal dan durasi akses.
                                        Bisa diubah nanti, dan fitur bisa di-toggle manual kapan saja.
                                    </td>
                                    <td><span class="badge bg-danger">Wajib</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Nama Admin</td>
                                    <td>Nama admin default yang akan mengelola sistem tenant. Hanya untuk referensi — belum otomatis membuat akun.</td>
                                    <td><span class="badge bg-secondary">Opsional</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Email Admin</td>
                                    <td>Email admin tenant. Untuk keperluan dokumentasi dan komunikasi.</td>
                                    <td><span class="badge bg-secondary">Opsional</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Catatan Internal</td>
                                    <td>Catatan pribadi super admin. Tidak terlihat oleh klien. Contoh: <em>"Klien prioritas, pembayaran tahunan"</em></td>
                                    <td><span class="badge bg-secondary">Opsional</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Yang terjadi otomatis --}}
                    <div class="alert alert-success mt-3 mb-0">
                        <strong><i class="bi bi-magic me-2"></i>Yang Terjadi Otomatis Setelah Klik "Buat Tenant":</strong>
                        <ol class="mb-0 mt-2">
                            <li>Record tenant disimpan ke database master</li>
                            <li>Database baru dibuat: <code>tenant_[subdomain]</code></li>
                            <li>Seluruh migration SIPERA dijalankan ke database baru</li>
                            <li>Fitur diinisialisasi sesuai paket yang dipilih</li>
                            <li>Sistem langsung bisa diakses di <code>[subdomain].apji.org</code></li>
                        </ol>
                    </div>
                </div>
            </div>

            {{-- 4. Kelola Fitur --}}
            <div class="card border-0 shadow-sm mb-4" id="kelola-fitur">
                <div class="card-header d-flex align-items-center gap-2" style="background:#cffafe;">
                    <span class="badge bg-info rounded-pill">4</span>
                    <strong>Cara Mengelola Fitur per Tenant</strong>
                </div>
                <div class="card-body">
                    <p>Setiap fitur bisa diaktifkan atau dinonaktifkan per klien kapan saja tanpa restart sistem.</p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold mb-2"><i class="bi bi-map me-2 text-primary"></i>Cara Akses</div>
                                <ol class="small mb-0">
                                    <li>Buka menu <strong>Manajemen Tenant</strong> di sidebar</li>
                                    <li>Klik ikon <i class="bi bi-gear"></i> pada baris tenant yang ingin diubah</li>
                                    <li>Di halaman detail, scroll ke bagian <strong>"Kelola Fitur"</strong></li>
                                    <li>Klik tombol toggle <i class="bi bi-toggle-on text-success"></i> / <i class="bi bi-toggle-off text-muted"></i> pada fitur yang diinginkan</li>
                                    <li>Perubahan langsung aktif — tidak perlu restart</li>
                                </ol>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold mb-2"><i class="bi bi-info-circle me-2 text-info"></i>Perilaku Toggle</div>
                                <ul class="small mb-0">
                                    <li><span class="badge bg-success">Hijau</span> = Fitur aktif, dapat digunakan klien</li>
                                    <li><span class="badge bg-light text-muted border">Abu</span> = Fitur nonaktif, menu/halaman tidak muncul</li>
                                    <li>Toggle tidak menghapus data — hanya menyembunyikan fitur</li>
                                    <li>Mengaktifkan kembali fitur akan memunculkan semua data lama</li>
                                    <li>Di portal master (<code>portal.apji.org</code>) semua fitur selalu aktif</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Catatan:</strong> Jika fitur <code>fasttrack</code> dinonaktifkan pada tenant, menu Fasttrack tidak akan muncul di sidebar klien tersebut, dan route-nya akan mengembalikan error 403 jika diakses langsung.
                    </div>
                </div>
            </div>

            {{-- 5. Paket & Fitur --}}
            <div class="card border-0 shadow-sm mb-4" id="paket">
                <div class="card-header d-flex align-items-center gap-2" style="background:#fef9c3;">
                    <span class="badge bg-warning text-dark rounded-pill">5</span>
                    <strong>Paket & Daftar Fitur</strong>
                </div>
                <div class="card-body">

                    {{-- Tabel Paket --}}
                    <h6 class="fw-semibold mb-2">Perbandingan Paket</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle text-center" style="font-size:0.83rem;">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-start">Fitur</th>
                                    <th>Trial<br><small class="fw-normal opacity-75">14 hari</small></th>
                                    <th>Basic<br><small class="fw-normal opacity-75">1 tahun</small></th>
                                    <th>Pro<br><small class="fw-normal opacity-75">1 tahun</small></th>
                                    <th>Enterprise<br><small class="fw-normal opacity-75">1 tahun</small></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $planMatrix = [
                                        'SMS Gateway / WhatsApp'  => [true,  true,  true,  true],
                                        'Laporan Harian PIC'      => [true,  true,  true,  true],
                                        'Modul Reviewer'          => [true,  true,  true,  true],
                                        'Export CSV'              => [true,  true,  true,  true],
                                        'Verifikasi LoA Publik'   => [true,  true,  true,  true],
                                        'Multi Jurnal'            => [true,  true,  true,  true],
                                        'Modul Marketing'         => [false, false, true,  true],
                                        'Fasttrack Submission'    => [false, false, true,  true],
                                        'Laporan BKD'             => [false, false, false, true],
                                        'JAFA Journal'            => [false, false, false, true],
                                    ];
                                @endphp
                                @foreach($planMatrix as $label => $cols)
                                <tr>
                                    <td class="text-start fw-semibold">{{ $label }}</td>
                                    @foreach($cols as $val)
                                    <td>
                                        @if($val)
                                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                        @else
                                            <i class="bi bi-x-circle text-muted fs-5"></i>
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Deskripsi Fitur --}}
                    <h6 class="fw-semibold mb-2">Penjelasan Setiap Fitur</h6>
                    <div class="accordion accordion-flush" id="accordionFitur">
                        @php
                            $fiturDesc = [
                                ['key'=>'sms_gateway',    'label'=>'SMS Gateway / WhatsApp', 'icon'=>'bi-whatsapp',      'color'=>'#4ade80', 'desc'=>'Notifikasi WhatsApp otomatis ke penulis artikel: saat submit, status berubah, dan artikel dipublikasi. Menggunakan API Fonnte.'],
                                ['key'=>'laporan_harian', 'label'=>'Laporan Harian PIC',     'icon'=>'bi-calendar-check','color'=>'#818cf8', 'desc'=>'Modul pencatatan kinerja harian untuk staf PIC. Termasuk validasi oleh admin, riwayat catatan, dan laporan tim.'],
                                ['key'=>'reviewer',       'label'=>'Modul Reviewer',          'icon'=>'bi-person-badge',  'color'=>'#67e8f9', 'desc'=>'Sistem penugasan dan manajemen reviewer artikel. Termasuk dashboard reviewer, leaderboard, dan sertifikat.'],
                                ['key'=>'marketing',      'label'=>'Modul Marketing',         'icon'=>'bi-megaphone',     'color'=>'#fcd34d', 'desc'=>'Manajemen staf marketing: target poin, riwayat submission, dan laporan performa marketing.'],
                                ['key'=>'fasttrack',      'label'=>'Fasttrack Submission',    'icon'=>'bi-lightning',     'color'=>'#f97316', 'desc'=>'Jalur percepatan publikasi artikel dengan penanganan prioritas. Termasuk monitoring khusus dan laporan fasttrack.'],
                                ['key'=>'export_csv',     'label'=>'Export CSV',              'icon'=>'bi-download',      'color'=>'#34d399', 'desc'=>'Tombol export data ke format CSV/Excel di berbagai halaman: daftar submission, reviewer, laporan kinerja, dll.'],
                                ['key'=>'loa_verify',     'label'=>'Verifikasi LoA Publik',   'icon'=>'bi-patch-check',   'color'=>'#a78bfa', 'desc'=>'Halaman publik untuk verifikasi keaslian Letter of Acceptance (LoA) artikel via kode submit.'],
                                ['key'=>'multi_journal',  'label'=>'Multi Jurnal',            'icon'=>'bi-journals',      'color'=>'#60a5fa', 'desc'=>'Kemampuan mengelola lebih dari satu jurnal dalam satu sistem. Termasuk slot, akreditasi, dan kategori per jurnal.'],
                                ['key'=>'bkd',            'label'=>'Laporan BKD',             'icon'=>'bi-file-earmark-text','color'=>'#fb923c', 'desc'=>'Modul laporan Beban Kerja Dosen (BKD) untuk artikel yang terindeks jurnal nasional/internasional.'],
                                ['key'=>'jafa',           'label'=>'JAFA Journal',            'icon'=>'bi-star',          'color'=>'#fbbf24', 'desc'=>'Modul khusus untuk jurnal JAFA dengan alur submission dan monitoring yang berbeda dari jurnal reguler.'],
                            ];
                        @endphp
                        @foreach($fiturDesc as $i => $f)
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#fitur{{ $i }}">
                                    <i class="bi {{ $f['icon'] }} me-2" style="color:{{ $f['color'] }};"></i>
                                    <span class="fw-semibold small">{{ $f['label'] }}</span>
                                    <code class="ms-2 text-muted" style="font-size:0.72rem;">{{ $f['key'] }}</code>
                                </button>
                            </h2>
                            <div id="fitur{{ $i }}" class="accordion-collapse collapse">
                                <div class="accordion-body py-2 text-muted small">{{ $f['desc'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 6. Suspend & Aktifkan --}}
            <div class="card border-0 shadow-sm mb-4" id="suspend">
                <div class="card-header d-flex align-items-center gap-2" style="background:#fee2e2;">
                    <span class="badge bg-danger rounded-pill">6</span>
                    <strong>Suspend & Aktifkan Tenant</strong>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border border-danger rounded p-3">
                                <div class="fw-semibold text-danger mb-2"><i class="bi bi-pause-circle me-2"></i>Suspend Tenant</div>
                                <ul class="small mb-0">
                                    <li>Buka halaman detail tenant</li>
                                    <li>Klik tombol <strong>"Suspend Tenant"</strong></li>
                                    <li>Konfirmasi di dialog yang muncul</li>
                                    <li>Status berubah menjadi <span class="badge bg-danger">Suspended</span></li>
                                    <li>Klien tidak bisa mengakses sistem (error 503)</li>
                                    <li><strong>Data tidak dihapus</strong> — bisa diaktifkan kembali kapan saja</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border border-success rounded p-3">
                                <div class="fw-semibold text-success mb-2"><i class="bi bi-play-circle me-2"></i>Aktifkan Kembali</div>
                                <ul class="small mb-0">
                                    <li>Buka halaman detail tenant yang suspended</li>
                                    <li>Klik tombol <strong>"Aktifkan Tenant"</strong></li>
                                    <li>Status berubah menjadi <span class="badge bg-success">Aktif</span></li>
                                    <li>Klien langsung bisa mengakses sistem kembali</li>
                                    <li>Semua data sebelumnya tetap utuh</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0 small">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>Kapan menggunakan Suspend?</strong> Saat klien belum membayar perpanjangan, sedang maintenance khusus, atau ada pelanggaran ketentuan layanan.
                    </div>
                </div>
            </div>

            {{-- 7. Migrasi Database --}}
            <div class="card border-0 shadow-sm mb-4" id="migrate">
                <div class="card-header d-flex align-items-center gap-2" style="background:#f1f5f9;">
                    <span class="badge bg-secondary rounded-pill">7</span>
                    <strong>Migrasi Database Tenant</strong>
                </div>
                <div class="card-body">
                    <p>Saat SIPERA mendapat pembaruan yang melibatkan perubahan struktur database (migration baru), jalankan migrasi ke semua tenant.</p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="fw-semibold mb-2"><i class="bi bi-globe me-2 text-primary"></i>Via Panel Web</div>
                                <p class="small mb-2"><strong>Semua Tenant:</strong></p>
                                <ol class="small mb-3">
                                    <li>Buka halaman <strong>Daftar Tenant</strong></li>
                                    <li>Klik tombol <strong>"Migrate Semua"</strong> di pojok kanan atas</li>
                                    <li>Konfirmasi → sistem akan menjalankan migration ke semua DB tenant</li>
                                </ol>
                                <p class="small mb-2"><strong>Satu Tenant:</strong></p>
                                <ol class="small mb-0">
                                    <li>Buka halaman <strong>Detail Tenant</strong></li>
                                    <li>Klik <strong>"Jalankan Migration"</strong> di panel Aksi</li>
                                </ol>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="fw-semibold mb-2"><i class="bi bi-terminal me-2 text-dark"></i>Via CLI (SSH)</div>
                                <div class="bg-dark text-white rounded p-2 small" style="font-family:monospace;">
                                    <div class="text-muted"># Migrate semua tenant</div>
                                    <div>php artisan tenants:migrate</div>
                                    <div class="mt-2 text-muted"># Migrate 1 tenant saja</div>
                                    <div>php artisan tenants:migrate<br>&nbsp;&nbsp;--tenant=univ-a</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning mb-0 small">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Kapan perlu migrate?</strong> Setiap kali ada pembaruan SIPERA yang disertai file migration baru di folder <code>database/migrations/</code>. Migrasi aman dijalankan berulang kali — tidak akan duplikasi tabel yang sudah ada.
                    </div>
                </div>
            </div>

            {{-- 8. Hapus Tenant --}}
            <div class="card border-0 shadow-sm mb-4" id="hapus">
                <div class="card-header d-flex align-items-center gap-2" style="background:#fee2e2;">
                    <span class="badge bg-danger rounded-pill">8</span>
                    <strong>Hapus Tenant (Permanen)</strong>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>PERINGATAN:</strong> Hapus tenant akan menghapus seluruh database klien secara permanen. <strong>Data tidak dapat dipulihkan.</strong> Pastikan sudah backup sebelum melanjutkan.
                    </div>

                    <div class="fw-semibold mb-2">Langkah Hapus Tenant:</div>
                    <ol class="small">
                        <li>Backup database tenant terlebih dahulu jika diperlukan:
                            <div class="bg-dark text-white rounded p-2 my-1" style="font-family:monospace;font-size:0.8rem;">
                                mysqldump -u [user] -p tenant_univ_a > backup_univ_a.sql
                            </div>
                        </li>
                        <li>Buka halaman <strong>Detail Tenant</strong> yang ingin dihapus</li>
                        <li>Scroll ke bawah, klik tombol <strong>"Hapus Tenant & Database"</strong></li>
                        <li>Modal konfirmasi akan muncul — <strong>ketik nama tenant</strong> untuk mengonfirmasi</li>
                        <li>Klik <strong>"Hapus Permanen"</strong></li>
                        <li>Sistem akan: hapus database, hapus record tenant dari master</li>
                    </ol>

                    <div class="alert alert-info mb-0 small">
                        <i class="bi bi-lightbulb me-2"></i>
                        Pertimbangkan <strong>Suspend</strong> terlebih dahulu sebelum hapus permanen — data masih bisa dipulihkan jika klien kembali.
                    </div>
                </div>
            </div>

            {{-- 9. CLI Commands --}}
            <div class="card border-0 shadow-sm mb-4" id="cli">
                <div class="card-header d-flex align-items-center gap-2" style="background:#1e293b;">
                    <span class="badge bg-dark border border-secondary rounded-pill">9</span>
                    <strong class="text-white">Referensi Perintah CLI</strong>
                </div>
                <div class="card-body bg-dark text-white rounded-bottom p-4" style="font-family:monospace;font-size:0.83rem;line-height:2;">
                    <div class="text-muted mb-2"># ─── Migrasi ───────────────────────────────────</div>
                    <div><span class="text-success">php artisan</span> tenants:migrate</div>
                    <div class="text-muted" style="font-size:0.75rem;line-height:1.2;margin-bottom:8px;">→ Migrate semua tenant</div>

                    <div><span class="text-success">php artisan</span> tenants:migrate <span class="text-yellow">--tenant</span>=univ-a</div>
                    <div class="text-muted" style="font-size:0.75rem;line-height:1.2;margin-bottom:8px;">→ Migrate 1 tenant saja</div>

                    <div class="text-muted mb-2"># ─── Cache & Optimasi ──────────────────────────</div>
                    <div><span class="text-success">php artisan</span> config:cache</div>
                    <div class="text-muted" style="font-size:0.75rem;line-height:1.2;margin-bottom:4px;">→ Cache konfigurasi (jalankan setelah ubah .env atau config/)</div>

                    <div><span class="text-success">php artisan</span> view:cache</div>
                    <div class="text-muted" style="font-size:0.75rem;line-height:1.2;margin-bottom:4px;">→ Cache Blade views</div>

                    <div><span class="text-success">php artisan</span> cache:clear</div>
                    <div class="text-muted" style="font-size:0.75rem;line-height:1.2;margin-bottom:8px;">→ Bersihkan semua cache</div>

                    <div class="text-muted mb-2"># ─── Deployment ────────────────────────────────</div>
                    <div><span class="text-cyan">git pull</span> && <span class="text-success">php artisan</span> config:cache && <span class="text-success">php artisan</span> tenants:migrate</div>
                    <div class="text-muted" style="font-size:0.75rem;line-height:1.2;">→ Urutan lengkap setelah update kode</div>
                </div>
            </div>

            {{-- 10. FAQ --}}
            <div class="card border-0 shadow-sm mb-4" id="faq">
                <div class="card-header d-flex align-items-center gap-2" style="background:#f8fafc;">
                    <span class="badge bg-secondary rounded-pill">10</span>
                    <strong>FAQ & Troubleshooting</strong>
                </div>
                <div class="card-body">
                    <div class="accordion" id="accordionFAQ">
                        @php
                            $faqs = [
                                [
                                    'q' => 'Gagal membuat tenant — "SQLSTATE: Access denied for CREATE"',
                                    'a' => 'MySQL user di .env belum punya izin CREATE DATABASE. Jalankan perintah GRANT di phpMyAdmin atau via SSH: <code>GRANT ALL PRIVILEGES ON `tenant_%`.* TO \'user_db\'@\'localhost\';</code>'
                                ],
                                [
                                    'q' => 'Subdomain klien tidak bisa diakses (ERR_NAME_NOT_RESOLVED)',
                                    'a' => 'Wildcard DNS belum dikonfigurasi. Tambahkan record A: <code>*.apji.org → IP server</code> di panel DNS domain Anda. Propagasi DNS bisa memakan waktu 1-24 jam.'
                                ],
                                [
                                    'q' => 'Apakah bisa menggunakan domain sendiri untuk klien? (bukan subdomain apji.org)',
                                    'a' => 'Bisa. Isi field "Custom Domain" di detail tenant (saat ini perlu diisi via database langsung karena belum ada field di form). Lalu klien perlu arahkan DNS domain mereka ke IP server SIPERA.'
                                ],
                                [
                                    'q' => 'Apakah data antar klien bisa saling melihat?',
                                    'a' => 'Tidak. Setiap klien punya database terpisah. TenantMiddleware memastikan setiap request hanya mengakses database milik tenant yang bersangkutan.'
                                ],
                                [
                                    'q' => 'Apa yang terjadi jika migrasi gagal di salah satu tenant?',
                                    'a' => 'Tenant lain tidak terpengaruh. Hasilnya akan ditampilkan di halaman (sukses/gagal per tenant). Cek log server di storage/logs/laravel.log untuk detail error.'
                                ],
                                [
                                    'q' => 'Apakah fitur yang dinonaktifkan menghapus data?',
                                    'a' => 'Tidak. Toggle fitur hanya menyembunyikan menu dan memblokir akses route. Data tetap ada di database. Mengaktifkan kembali fitur akan memunculkan semua data sebelumnya.'
                                ],
                                [
                                    'q' => 'Bagaimana cara membuat akun admin untuk tenant baru?',
                                    'a' => 'Saat ini harus dilakukan manual: masuk ke phpMyAdmin, pilih database tenant (misal tenant_univ_a), lalu buat record di tabel users. Atau tambahkan seeder khusus di TenantManager::create().'
                                ],
                                [
                                    'q' => 'Berapa banyak tenant yang bisa dibuat?',
                                    'a' => 'Tidak ada batas dari sisi aplikasi. Batasnya adalah kapasitas server (RAM, CPU, disk). Disarankan monitoring performa server saat jumlah tenant bertambah banyak.'
                                ],
                            ];
                        @endphp
                        @foreach($faqs as $i => $faq)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed small fw-semibold" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq{{ $i }}">
                                    <i class="bi bi-question-circle text-primary me-2"></i>{{ $faq['q'] }}
                                </button>
                            </h2>
                            <div id="faq{{ $i }}" class="accordion-collapse collapse">
                                <div class="accordion-body small text-muted">{!! $faq['a'] !!}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- CTA --}}
            <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                <div class="card-body text-white text-center py-4">
                    <h5 class="fw-bold mb-2">Siap Membuat Tenant Pertama?</h5>
                    <p class="opacity-75 small mb-3">Pastikan prasyarat server sudah terpenuhi, lalu klik tombol di bawah.</p>
                    <a href="{{ route('admin.tenants.create') }}" class="btn btn-light btn-sm px-4 fw-semibold">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Tenant Sekarang
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Smooth scroll untuk daftar isi
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        const el = document.querySelector(a.getAttribute('href'));
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
</script>
@endsection
