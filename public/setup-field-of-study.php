<?php
/**
 * Script untuk populate Field of Studies
 * Akses via: http://portal.apji.org/setup-field-of-study.php
 * 
 * HAPUS FILE INI SETELAH SELESAI!
 */

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// Check if admin
if (!auth()->check() || auth()->user()->role !== 'admin') {
    die('<h1>Access Denied</h1><p>Hanya admin yang bisa akses script ini.</p>');
}

$fields = [
    ['name' => 'Pertanian (Agriculture)', 'description' => 'Ilmu pertanian, perkebunan, kehutanan', 'order' => 1],
    ['name' => 'Seni (Art)', 'description' => 'Seni rupa, musik, desain', 'order' => 2],
    ['name' => 'Ekonomi (Economics)', 'description' => 'Ekonomi, bisnis, manajemen, akuntansi', 'order' => 3],
    ['name' => 'Pendidikan (Education)', 'description' => 'Ilmu pendidikan, pembelajaran', 'order' => 4],
    ['name' => 'Teknik (Engineering)', 'description' => 'Teknik sipil, elektro, mesin, informatika', 'order' => 5],
    ['name' => 'Kesehatan (Health)', 'description' => 'Kedokteran, keperawatan, farmasi, kesehatan masyarakat', 'order' => 6],
    ['name' => 'Humaniora (Humanities)', 'description' => 'Sastra, bahasa, sejarah, filsafat', 'order' => 7],
    ['name' => 'Agama (Religion)', 'description' => 'Studi agama, teologi', 'order' => 8],
    ['name' => 'Sains (Science)', 'description' => 'Matematika, fisika, kimia, biologi', 'order' => 9],
    ['name' => 'Sosial (Social)', 'description' => 'Sosiologi, politik, hukum, psikologi', 'order' => 10],
];

$action = $_GET['action'] ?? 'check';
$existingFields = \App\Models\FieldOfStudy::all();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Field of Study</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">🎓 Setup Field of Study (Bidang Ilmu)</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($action === 'populate'): ?>
                            <?php
                            $inserted = 0;
                            $updated = 0;
                            foreach ($fields as $field) {
                                $existing = \App\Models\FieldOfStudy::where('name', $field['name'])->first();
                                if ($existing) {
                                    $existing->update([
                                        'description' => $field['description'],
                                        'is_active' => true,
                                        'order' => $field['order']
                                    ]);
                                    $updated++;
                                } else {
                                    \App\Models\FieldOfStudy::create([
                                        'name' => $field['name'],
                                        'description' => $field['description'],
                                        'is_active' => true,
                                        'order' => $field['order']
                                    ]);
                                    $inserted++;
                                }
                            }
                            ?>
                            <div class="alert alert-success">
                                <h5>✅ Berhasil!</h5>
                                <p><strong><?= $inserted ?></strong> bidang ilmu baru ditambahkan</p>
                                <p><strong><?= $updated ?></strong> bidang ilmu diupdate</p>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama</th>
                                            <th>Deskripsi</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (\App\Models\FieldOfStudy::ordered()->get() as $field): ?>
                                        <tr>
                                            <td><?= $field->id ?></td>
                                            <td><strong><?= $field->name ?></strong></td>
                                            <td><?= $field->description ?></td>
                                            <td>
                                                <span class="badge bg-<?= $field->is_active ? 'success' : 'secondary' ?>">
                                                    <?= $field->is_active ? 'Aktif' : 'Nonaktif' ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-info mt-3">
                                <h6>✅ Selanjutnya:</h6>
                                <ol>
                                    <li>Buka <a href="/admin/assignments/create" target="_blank">/admin/assignments/create</a></li>
                                    <li>Dropdown "Bidang/Section/Topik" sudah muncul dengan 10 pilihan</li>
                                    <li>Pilih bidang saat buat assignment baru</li>
                                    <li>Reviewer akan otomatis dapat field tersebut (readonly)</li>
                                </ol>
                            </div>

                            <div class="alert alert-warning">
                                <strong>⚠️ PENTING:</strong> Hapus file <code>public/setup-field-of-study.php</code> setelah selesai!
                            </div>

                            <a href="/admin/assignments/create" class="btn btn-success btn-lg">
                                🚀 Buat Assignment Baru
                            </a>

                        <?php else: ?>
                            <div class="alert alert-info">
                                <h5>📊 Status Saat Ini</h5>
                                <p>Ditemukan: <strong><?= $existingFields->count() ?></strong> bidang ilmu</p>
                            </div>

                            <?php if ($existingFields->count() > 0): ?>
                                <div class="table-responsive mb-4">
                                    <table class="table table-striped">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Nama</th>
                                                <th>Deskripsi</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($existingFields as $field): ?>
                                            <tr>
                                                <td><?= $field->id ?></td>
                                                <td><strong><?= $field->name ?></strong></td>
                                                <td><?= $field->description ?? '-' ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $field->is_active ? 'success' : 'secondary' ?>">
                                                        <?= $field->is_active ? 'Aktif' : 'Nonaktif' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>

                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5>📝 Bidang Ilmu yang Akan Ditambahkan/Update:</h5>
                                    <ol>
                                        <?php foreach ($fields as $field): ?>
                                        <li><strong><?= $field['name'] ?></strong> - <?= $field['description'] ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <a href="?action=populate" class="btn btn-primary btn-lg">
                                    ✅ Populate/Update Bidang Ilmu Sekarang
                                </a>
                                <a href="/admin/assignments" class="btn btn-secondary">Batal</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
