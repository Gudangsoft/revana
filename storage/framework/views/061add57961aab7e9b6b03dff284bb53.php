

<?php $__env->startSection('title', 'Reward Management - REVANA'); ?>
<?php $__env->startSection('page-title', 'Manajemen Reward'); ?>

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
<div class="row mb-3">
    <div class="col">
        <a href="<?php echo e(route('admin.rewards.create')); ?>" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Tambah Reward Baru
        </a>
    </div>
</div>

<?php if(session('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> <?php echo e(session('success')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if(session('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i> <?php echo e(session('error')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <i class="bi bi-trophy"></i> Daftar Reward
    </div>
    <div class="card-body">
        <?php if($rewards->isEmpty()): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada reward yang dibuat.
            <a href="<?php echo e(route('admin.rewards.create')); ?>">Buat reward pertama</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Reward</th>
                        <th>Tipe</th>                        <th>Peringkat</th>                        <th>Points Required</th>
                        <th>Value</th>
                        <th>Total Redeemed</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $rewards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reward): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($reward->id); ?></td>
                        <td>
                            <div class="fw-bold"><?php echo e($reward->name); ?></div>
                            <?php if($reward->description): ?>
                                <small class="text-muted"><?php echo e(Str::limit($reward->description, 50)); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo e($reward->type); ?></span>
                        </td>
                        <td>
                            <?php if($reward->tier == 'Platinum'): ?>
                                <span class="badge" style="background: linear-gradient(135deg, #b7a1d8, #7c3aed);">💎 Platinum</span>
                            <?php elseif($reward->tier == 'Gold'): ?>
                                <span class="badge" style="background: linear-gradient(135deg, #fcd34d, #f59e0b);">🥇 Gold</span>
                            <?php elseif($reward->tier == 'Silver'): ?>
                                <span class="badge" style="background: linear-gradient(135deg, #cbd5e1, #64748b);">🥈 Silver</span>
                            <?php else: ?>
                                <span class="badge" style="background: linear-gradient(135deg, #d97706, #92400e); color: white;">🥉 Bronze</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-warning text-dark">
                                <?php echo e(number_format($reward->points_required)); ?> pts
                            </span>
                        </td>
                        <td>
                            <?php if($reward->value): ?>
                                Rp <?php echo e(number_format($reward->value, 0, ',', '.')); ?>

                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo e($reward->redemptions_count); ?> kali</span>
                        </td>
                        <td>
                            <?php if($reward->is_active): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Aktif
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle"></i> Nonaktif
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?php echo e(route('admin.rewards.edit', $reward)); ?>" 
                                   class="btn btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="<?php echo e(route('admin.rewards.toggle', $reward)); ?>" 
                                      method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" 
                                            class="btn btn-<?php echo e($reward->is_active ? 'secondary' : 'success'); ?>" 
                                            title="<?php echo e($reward->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>">
                                        <i class="bi bi-<?php echo e($reward->is_active ? 'pause' : 'play'); ?>-circle"></i>
                                    </button>
                                </form>
                                <?php if($reward->redemptions_count == 0): ?>
                                <form action="<?php echo e(route('admin.rewards.destroy', $reward)); ?>" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus reward ini?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            <?php echo e($rewards->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Summary -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-muted">Total Rewards</h5>
                <h2><?php echo e($rewards->total()); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-success">Active</h5>
                <h2><?php echo e($rewards->where('is_active', true)->count()); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-secondary">Inactive</h5>
                <h2><?php echo e($rewards->where('is_active', false)->count()); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-info">Total Redeemed</h5>
                <h2><?php echo e($rewards->sum('redemptions_count')); ?></h2>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/admin/rewards/index.blade.php ENDPATH**/ ?>