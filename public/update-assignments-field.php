<?php
/**
 * Script untuk update field_of_study_id pada assignment lama
 * Akses via: http://portal.apji.org/update-assignments-field.php?field_id=1&confirm=yes
 * 
 * HAPUS FILE INI SETELAH SELESAI UPDATE!
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

// Get parameters
$fieldId = $_GET['field_id'] ?? null;
$confirm = $_GET['confirm'] ?? null;

// Get all field of studies
$fields = \App\Models\FieldOfStudy::active()->ordered()->get();

// Get assignments without field_of_study_id
$assignments = \App\Models\ReviewAssignment::whereNull('field_of_study_id')->get();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Assignment Field of Study</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">🔧 Update Assignment Field of Study</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($assignments->isEmpty()): ?>
                            <div class="alert alert-success">
                                <h5>✅ Semua Assignment Sudah Lengkap!</h5>
                                <p>Tidak ada assignment yang perlu diupdate.</p>
                            </div>
                            <a href="/admin/assignments" class="btn btn-primary">Kembali ke Dashboard</a>
                        <?php elseif ($confirm === 'yes' && $fieldId): ?>
                            <?php
                            // Validate field exists
                            $selectedField = \App\Models\FieldOfStudy::find($fieldId);
                            if (!$selectedField) {
                                echo '<div class="alert alert-danger">Field of Study tidak ditemukan!</div>';
                            } else {
                                // Update assignments
                                $updated = 0;
                                foreach ($assignments as $assignment) {
                                    $assignment->field_of_study_id = $fieldId;
                                    if ($assignment->save()) {
                                        $updated++;
                                    }
                                }
                                ?>
                                <div class="alert alert-success">
                                    <h5>✅ Update Berhasil!</h5>
                                    <p><strong><?= $updated ?></strong> assignment telah diupdate dengan bidang ilmu: <strong><?= $selectedField->name ?></strong></p>
                                </div>
                                <div class="alert alert-warning">
                                    <strong>⚠️ PENTING:</strong> Hapus file <code>public/update-assignments-field.php</code> ini setelah selesai!
                                </div>
                                <a href="/admin/assignments" class="btn btn-primary">Kembali ke Dashboard</a>
                                <?php
                            }
                            ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <h5>⚠️ Ada <?= $assignments->count() ?> Assignment Belum Punya Bidang Ilmu</h5>
                                <p>Assignment yang akan diupdate:</p>
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="5%">ID</th>
                                            <th width="40%">Judul Artikel</th>
                                            <th width="25%">Reviewer</th>
                                            <th width="15%">Status</th>
                                            <th width="15%">Deadline</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assignments as $assignment): ?>
                                        <tr>
                                            <td><?= $assignment->id ?></td>
                                            <td><?= $assignment->article_title ?></td>
                                            <td><?= $assignment->reviewer->name ?? '-' ?></td>
                                            <td>
                                                <span class="badge bg-<?= $assignment->status === 'ON_PROGRESS' ? 'info' : 'secondary' ?>">
                                                    <?= $assignment->status ?>
                                                </span>
                                            </td>
                                            <td><?= $assignment->deadline->format('d/m/Y') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <form method="GET" class="border p-4 rounded bg-light">
                                <h5 class="mb-3">Pilih Bidang Ilmu untuk Semua Assignment:</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Bidang Ilmu <span class="text-danger">*</span></label>
                                    <select name="field_id" class="form-select form-select-lg" required>
                                        <option value="">-- Pilih Bidang Ilmu --</option>
                                        <?php foreach ($fields as $field): ?>
                                        <option value="<?= $field->id ?>">
                                            <?= $field->name ?>
                                            <?php if ($field->description): ?>
                                                (<?= $field->description ?>)
                                            <?php endif; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">
                                        Semua assignment di atas akan diset ke bidang ilmu yang Anda pilih.
                                    </small>
                                </div>

                                <input type="hidden" name="confirm" value="yes">
                                
                                <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Yakin update <?= $assignments->count() ?> assignments?')">
                                    ✅ Update Sekarang
                                </button>
                                <a href="/admin/assignments" class="btn btn-secondary btn-lg">Batal</a>
                            </form>

                            <div class="alert alert-info mt-4">
                                <h6>💡 Alternatif: Update Manual per Assignment</h6>
                                <p>Jika ingin set bidang berbeda-beda per assignment, gunakan SQL:</p>
                                <pre class="bg-dark text-light p-3 rounded"><code>-- Update assignment tertentu
UPDATE review_assignments SET field_of_study_id = 1 WHERE id = 17;
UPDATE review_assignments SET field_of_study_id = 2 WHERE id = 18;</code></pre>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center mt-3 text-muted">
                    <small>Script Update v1.0 - <?= date('Y-m-d H:i:s') ?></small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
