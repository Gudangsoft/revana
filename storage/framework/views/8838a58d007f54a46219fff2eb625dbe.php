

<?php $__env->startSection('title', 'Edit Reward - REVANA'); ?>
<?php $__env->startSection('page-title', 'Edit Reward'); ?>

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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy"></i> Form Edit Reward</span>
                <?php if($reward->redemptions_count > 0): ?>
                <span class="badge bg-warning">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo e($reward->redemptions_count); ?> redemptions
                </span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form action="<?php echo e(route('admin.rewards.update', $reward)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

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
                               value="<?php echo e(old('name', $reward->name)); ?>"
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
                                  placeholder="Jelaskan detail reward ini..."><?php echo e(old('description', $reward->description)); ?></textarea>
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
                               value="<?php echo e(old('type', $reward->type)); ?>"
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
                            <option value="Bronze" <?php echo e(old('tier', $reward->tier) == 'Bronze' ? 'selected' : ''); ?>>
                                🥉 Bronze (Entry Level)
                            </option>
                            <option value="Silver" <?php echo e(old('tier', $reward->tier) == 'Silver' ? 'selected' : ''); ?>>
                                🥈 Silver (Standard)
                            </option>
                            <option value="Gold" <?php echo e(old('tier', $reward->tier) == 'Gold' ? 'selected' : ''); ?>>
                                🥇 Gold (Premium)
                            </option>
                            <option value="Platinum" <?php echo e(old('tier', $reward->tier) == 'Platinum' ? 'selected' : ''); ?>>
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
                               value="<?php echo e(old('points_required', $reward->points_required)); ?>"
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
                               value="<?php echo e(old('value', $reward->value)); ?>"
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
                                   <?php echo e(old('is_active', $reward->is_active) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_active">
                                Aktifkan reward ini
                            </label>
                        </div>
                        <small class="text-muted">Reward yang aktif bisa ditukar oleh reviewer</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Update Reward
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
                <i class="bi bi-info-circle"></i> Info Reward
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td class="fw-bold">Status:</td>
                        <td>
                            <?php if($reward->is_active): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Total Redemptions:</td>
                        <td><span class="badge bg-primary"><?php echo e($reward->redemptions_count); ?></span></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Dibuat:</td>
                        <td><?php echo e($reward->created_at->format('d M Y H:i')); ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Terakhir Update:</td>
                        <td><?php echo e($reward->updated_at->format('d M Y H:i')); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-warning">
                <i class="bi bi-exclamation-triangle"></i> Perhatian
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <?php if($reward->redemptions_count > 0): ?>
                    <li class="text-danger">Reward ini sudah memiliki <?php echo e($reward->redemptions_count); ?> redemptions</li>
                    <li>Hati-hati mengubah points required</li>
                    <?php else: ?>
                    <li>Reward ini belum ada yang menukar</li>
                    <?php endif; ?>
                    <li>Nonaktifkan jika reward sudah tidak tersedia</li>
                    <li>Perubahan langsung berlaku untuk reviewer</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/admin/rewards/edit.blade.php ENDPATH**/ ?>