

<?php $__env->startSection('title', 'Upload Hasil Review - REVANA'); ?>
<?php $__env->startSection('page-title', 'Upload Hasil Review'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="<?php echo e(route('reviewer.dashboard')); ?>" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?php echo e(route('reviewer.tasks.index')); ?>" class="nav-link active">
        <i class="bi bi-clipboard-check"></i> My Tasks
    </a>
    <a href="<?php echo e(route('reviewer.rewards.index')); ?>" class="nav-link">
        <i class="bi bi-gift"></i> Rewards
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-upload"></i> Form Upload Hasil Review
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Jurnal:</strong> <?php echo e($assignment->journal->title); ?><br>
                    <strong>Reward:</strong> <span class="badge bg-success"><?php echo e($assignment->journal->points); ?> Points</span>
                </div>

                <form action="<?php echo e(route('reviewer.results.store', $assignment)); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label">Link Google Drive Hasil Review <span class="text-danger">*</span></label>
                        <input type="url" class="form-control <?php $__errorArgs = ['google_drive_link'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               name="google_drive_link" 
                               value="<?php echo e(old('google_drive_link')); ?>"
                               placeholder="https://drive.google.com/file/d/..." required>
                        <?php $__errorArgs = ['google_drive_link'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Pastikan file sudah di-set "Anyone with the link can view"
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Review (Opsional)</label>
                        <textarea class="form-control <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                  name="notes" rows="8"
                                  placeholder="Tuliskan ringkasan hasil review Anda, poin-poin penting, saran, dan kesimpulan... (Opsional)"><?php echo e(old('notes')); ?></textarea>
                        <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="text-muted">Catatan review bersifat opsional, tetapi direkomendasikan untuk memperjelas hasil review</small>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> 
                        <ul class="mb-0 mt-2">
                            <li>Pastikan link Google Drive dapat diakses</li>
                            <li>File review sudah lengkap dan sesuai format</li>
                            <li>Catatan berisi ringkasan penting dari review</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-send"></i> Submit Review
                        </button>
                        <a href="<?php echo e(route('reviewer.tasks.show', $assignment)); ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Cara Upload ke Google Drive
            </div>
            <div class="card-body">
                <h6>Langkah-langkah:</h6>
                <ol class="small">
                    <li>Upload file review ke Google Drive</li>
                    <li>Klik kanan file → <strong>Share</strong></li>
                    <li>Pilih <strong>Anyone with the link</strong></li>
                    <li>Set permission <strong>Viewer</strong></li>
                    <li>Klik <strong>Copy link</strong></li>
                    <li>Paste link di form</li>
                </ol>
                <hr>
                <h6>Checklist:</h6>
                <ul class="small">
                    <li>✅ Review sudah lengkap</li>
                    <li>✅ Link dapat diakses</li>
                    <li>✅ Catatan sudah diisi</li>
                </ul>
                <hr>
                <small class="text-muted">
                    Setelah submit, admin akan validasi. Jika disetujui, dapat 
                    <strong><?php echo e($assignment->journal->points); ?> points</strong>.
                </small>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-trophy"></i> Reward
            </div>
            <div class="card-body text-center">
                <h2 class="text-success"><?php echo e($assignment->journal->points); ?></h2>
                <p class="mb-0">Points akan didapat jika disetujui</p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/reviewer/results/create.blade.php ENDPATH**/ ?>