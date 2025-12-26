

<?php $__env->startSection('title', 'Tambah Reward - REVANA'); ?>
<?php $__env->startSection('page-title', 'Tambah Reward Baru'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?php echo e(route('admin.journals.index')); ?>" class="nav-link">
        <i class="bi bi-journal-text"></i> Jurnal
    </a>
    <a href="<?php echo e(route('admin.assignments.index')); ?>" class="nav-link">
        <i class="bi bi-clipboard-check"></i> Review Assignments
    </a>
    <a href="<?php echo e(route('admin.reviewers.index')); ?>" class="nav-link">
        <i class="bi bi-people"></i> Reviewers
    </a>
    <a href="<?php echo e(route('admin.leaderboard.index')); ?>" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="<?php echo e(route('admin.redemptions.index')); ?>" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
    <a href="<?php echo e(route('admin.points.index')); ?>" class="nav-link">
        <i class="bi bi-coin"></i> Point Management
    </a>
    <a href="<?php echo e(route('admin.rewards.index')); ?>" class="nav-link active">
        <i class="bi bi-trophy"></i> Reward Management
    </a>
    <a href="<?php echo e(route('admin.marketings.index')); ?>" class="nav-link">
        <i class="bi bi-megaphone"></i> Marketing
    </a>
    <a href="<?php echo e(route('admin.pics.index')); ?>" class="nav-link">
        <i class="bi bi-person-badge"></i> PIC
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-trophy"></i> Form Tambah Reward
            </div>
            <div class="card-body">
                <form action="<?php echo e(route('admin.rewards.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label">Nama Reward <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               name="name" 
                               value="<?php echo e(old('name')); ?>"
                               required
                               placeholder="Contoh: Voucher Pulsa 50K">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                  name="description" rows="3"
                                  placeholder="Jelaskan detail reward ini..."><?php echo e(old('description')); ?></textarea>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipe Reward <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               name="type" 
                               value="<?php echo e(old('type')); ?>"
                               required
                               placeholder="Contoh: Voucher, E-Wallet, Merchandise, Cash">
                        <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="text-muted">Tipe/kategori reward (Voucher, E-Wallet, Cash, dll)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Peringkat Reward <span class="text-danger">*</span></label>
                        <select class="form-select <?php $__errorArgs = ['tier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="tier" required>
                            <option value="">Pilih Peringkat</option>
                            <option value="Bronze" <?php echo e(old('tier') == 'Bronze' ? 'selected' : ''); ?>>
                                🥉 Bronze (Entry Level)
                            </option>
                            <option value="Silver" <?php echo e(old('tier') == 'Silver' ? 'selected' : ''); ?>>
                                🥈 Silver (Standard)
                            </option>
                            <option value="Gold" <?php echo e(old('tier') == 'Gold' ? 'selected' : ''); ?>>
                                🥇 Gold (Premium)
                            </option>
                            <option value="Platinum" <?php echo e(old('tier') == 'Platinum' ? 'selected' : ''); ?>>
                                💎 Platinum (Exclusive)
                            </option>
                        </select>
                        <?php $__errorArgs = ['tier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="text-muted">Tingkat/level reward untuk sistem ranking</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Points Required <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control <?php $__errorArgs = ['points_required'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               name="points_required" 
                               value="<?php echo e(old('points_required')); ?>"
                               min="1" 
                               required>
                        <?php $__errorArgs = ['points_required'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="text-muted">Jumlah poin yang dibutuhkan untuk menukar reward ini</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Value (Rupiah)</label>
                        <input type="number" 
                               class="form-control <?php $__errorArgs = ['value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               name="value" 
                               value="<?php echo e(old('value')); ?>"
                               min="0"
                               step="0.01"
                               placeholder="Contoh: 50000">
                        <?php $__errorArgs = ['value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="text-muted">Nilai reward dalam rupiah (opsional)</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="is_active" 
                                   id="is_active"
                                   <?php echo e(old('is_active', true) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_active">
                                Aktifkan reward ini
                            </label>
                        </div>
                        <small class="text-muted">Reward yang aktif bisa ditukar oleh reviewer</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Simpan Reward
                        </button>
                        <a href="<?php echo e(route('admin.rewards.index')); ?>" class="btn btn-secondary">
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
                <i class="bi bi-info-circle"></i> Panduan
            </div>
            <div class="card-body">
                <h6>Contoh Reward:</h6>
                <ul class="small">
                    <li><strong>Voucher Pulsa 50K</strong><br>Tipe: Voucher, Points: 100</li>
                    <li><strong>Gopay 100K</strong><br>Tipe: E-Wallet, Points: 200</li>
                    <li><strong>Cash 500K</strong><br>Tipe: Cash, Points: 1000</li>
                    <li><strong>Sertifikat Review</strong><br>Tipe: Certificate, Points: 50</li>
                </ul>
                <hr>
                <p class="small mb-0">
                    Sesuaikan points required dengan value reward agar sistem point tetap seimbang.
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-warning">
                <i class="bi bi-lightbulb"></i> Tips
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Buat reward yang menarik untuk reviewer</li>
                    <li>Tetapkan points yang fair</li>
                    <li>Update deskripsi secara jelas</li>
                    <li>Nonaktifkan reward yang sudah tidak tersedia</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/admin/rewards/create.blade.php ENDPATH**/ ?>